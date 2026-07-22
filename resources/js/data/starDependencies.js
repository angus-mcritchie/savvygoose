const SESSION_KEY = 'star-dependencies:pending';

function detectType(data) {
    if (data && typeof data === 'object' && (data.require || data['require-dev'])) return 'composer';
    if (data && typeof data === 'object' && (data.dependencies || data.devDependencies)) return 'npm';
    return null;
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

async function postJson(url, body) {
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(body),
    });
    const data = await res.json().catch(() => ({}));
    return { ok: res.ok, status: res.status, data };
}

// This tool deliberately skips withUrlState/<x-share-field>: a full manifest
// plus its resolved repo list isn't meaningful or safe to carry in a
// shareable URL, unlike every other tool's state. Don't "fix" that.
export default ({ connected = false } = {}) => ({
    manifestText: '',
    manifestType: '',
    dependencies: [],
    connected,
    resolving: false,
    starring: false,
    error: '',
    fileError: '',

    init() {
        const params = new URLSearchParams(window.location.search);
        if (params.get('connected') === '1') {
            this.connected = true;
            params.delete('connected');
            const qs = params.toString();
            history.replaceState(null, '', window.location.pathname + (qs ? `?${qs}` : ''));
        }
        if (params.get('auth_error') === '1') {
            this.error = "GitHub sign-in didn't complete. You can try connecting again when you're ready.";
            params.delete('auth_error');
            const qs = params.toString();
            history.replaceState(null, '', window.location.pathname + (qs ? `?${qs}` : ''));
        }

        const saved = sessionStorage.getItem(SESSION_KEY);
        if (!saved) return;
        sessionStorage.removeItem(SESSION_KEY);
        try {
            const state = JSON.parse(saved);
            this.manifestText = state.manifestText || '';
            this.manifestType = state.manifestType || '';
            this.dependencies = state.dependencies || [];
        } catch (_) {
            // Corrupt sessionStorage — just start fresh.
        }
    },

    get selected() {
        return this.dependencies.filter((d) => d.selected && d.resolved);
    },

    get resolvableCount() {
        return this.dependencies.filter((d) => d.resolved).length;
    },

    onFileSelected(event) {
        const file = event.target.files[0];
        event.target.value = '';
        if (!file) return;

        this.fileError = '';
        const reader = new FileReader();
        reader.onload = () => {
            this.manifestText = String(reader.result || '');
        };
        reader.onerror = () => {
            this.fileError = "Couldn't read that file.";
        };
        reader.readAsText(file);
    },

    async resolve() {
        this.error = '';
        this.dependencies = [];

        let data;
        try {
            data = JSON.parse(this.manifestText);
        } catch (_) {
            this.error = "That doesn't look like valid JSON.";
            return;
        }

        const type = detectType(data);
        if (!type) {
            this.error = "Couldn't find dependencies in that manifest. Paste a package.json or composer.json.";
            return;
        }
        this.manifestType = type;

        this.resolving = true;
        try {
            const { ok, status, data: body } = await postJson('/api/star-dependencies/resolve', {
                manifest: this.manifestText,
                type,
            });
            if (!ok) {
                this.error = body.message || `Couldn't resolve dependencies (${status}).`;
                return;
            }
            this.dependencies = (body.dependencies || []).map((d) => ({ ...d, selected: d.resolved, status: null }));
        } catch (_) {
            this.error = "Couldn't reach the server. Check your connection.";
        } finally {
            this.resolving = false;
        }
    },

    toggleAll(value) {
        this.dependencies.forEach((d) => {
            if (d.resolved) d.selected = value;
        });
    },

    connectGithub() {
        sessionStorage.setItem(SESSION_KEY, JSON.stringify({
            manifestText: this.manifestText,
            manifestType: this.manifestType,
            dependencies: this.dependencies,
        }));
        window.location.href = '/auth/github/redirect';
    },

    async starSelected() {
        const repos = this.selected.map((d) => ({ owner: d.owner, repo: d.repo }));
        if (!repos.length) return;

        this.starring = true;
        this.error = '';
        try {
            const { ok, status, data: body } = await postJson('/api/star-dependencies/star', { repos });
            if (!ok) {
                if (status === 403) this.connected = false;
                this.error = body.message || `Couldn't star those repos (${status}).`;
                return;
            }
            (body.results || []).forEach((result) => {
                const dep = this.dependencies.find((d) => d.owner === result.owner && d.repo === result.repo);
                if (dep) dep.status = result.starred ? 'starred' : 'failed';
            });
        } catch (_) {
            this.error = "Couldn't reach the server. Check your connection.";
        } finally {
            this.starring = false;
        }
    },

    async disconnect() {
        try {
            const response = await fetch('/auth/github/disconnect', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken() },
            });
            const body = await response.json().catch(() => ({}));
            if (!response.ok) {
                this.error = body.message || "Couldn't disconnect from GitHub. Try again.";
                return;
            }
            this.connected = false;
            if (body.revoked === false) {
                this.error = 'Disconnected here, but GitHub could not confirm the authorization was revoked. Remove Savvy Goose from your GitHub application settings.';
            }
        } catch {
            this.error = "Couldn't reach the server to disconnect. Check your connection and try again.";
        }
    },
});
