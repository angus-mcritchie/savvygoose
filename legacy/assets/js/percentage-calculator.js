$(document).ready(function () {
    
    const elems = {
        form1: {
            form: $('#form-1'),
            x: $('#form-1-x'),
            y: $('#form-1-y'),
            btn: $('#form-1-btn'),
            result: $('#form-1-result')
        },
        form2: {
            form: $('#form-2'),
            x: $('#form-2-x'),
            y: $('#form-2-y'),
            btn: $('#form-2-btn'),
            result: $('#form-2-result')
        },
        form3: {
            form: $('#form-3'),
            x: $('#form-3-x'),
            y: $('#form-3-y'),
            btn: $('#form-3-btn'),
            result: $('#form-3-result')
        }
    };

    elems.form1.form.submit(function (e) {
        e.preventDefault();
        
        const {x, y, result} = elems.form1;
        const resultVal = getForm1Result(x.val(), y.val());
        
        result.text(`${resultVal.toFixed(2)}`);

    });

    elems.form2.form.submit(function (e) {
        e.preventDefault();
        
        const {x, y, result} = elems.form2;
        const resultVal = getForm2Result(x.val(), y.val());
        
        result.text(`${resultVal.toFixed(2)}%`);

    });

    elems.form3.form.submit(function (e) {
        e.preventDefault();
        
        const {x, y, result} = elems.form3;
        const resultVal = getForm3Result(x.val(), y.val());
        
        result.text(`${resultVal.toFixed(2)}%`);

    });

    function getForm1Result(x, y) {
        return y * (x / 100);
    }

    function getForm2Result(x, y) {
        return (x / y) * 100;
    }

    function getForm3Result(x, y) {
        return ((y - x) / x) * 100;
    }

});
