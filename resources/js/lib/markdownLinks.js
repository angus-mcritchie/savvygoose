// Links that carry a Markdown document from one tool to another.
//
// The field definitions here are the ones the receiving tool puts in its own
// url-state schema, so a link built on this side is one the other side will
// accept: same plain-text cap, same compressed budget, same key. Import the
// definition rather than retyping it, or the two drift and the link opens blank.

import { toQueryParam } from './urlState';

export const DOCUMENT_FIELD = { type: 'string', maxLength: 3000 };
export const CONVERTER_INPUT_FIELD = { type: 'string', maxLength: 3000 };

function link(path, urlKey, field, markdown) {
    const param = markdown ? toQueryParam(urlKey, markdown, field) : null;
    if (!param) return '';

    return `${window.location.origin}${path}?${new URLSearchParams([param])}`;
}

// Both return '' when the text does not fit in a URL even compressed, which is
// the caller's cue to say so rather than hand over a link that opens empty.
export const documentUrl = (markdown) => link('/document-viewer', 'd', DOCUMENT_FIELD, markdown);
export const converterUrl = (markdown) => link('/markdown-converter', 'input', CONVERTER_INPUT_FIELD, markdown);
