(function(d) {
    var table = d.querySelector('.questions');
    table.addEventListener( 'click', function(e) {
        var label, input;
        if (e.target.nodeName === 'LABEL') {
            label = e.target;
            input = label.querySelector('input');
        } else if (e.target.nodeName === 'INPUT') {
            input = e.target;
            label = input.parentNode;
        } else {
            return;
        }
        if (input.checked) {
            label.classList.add('checked-label');
        } else {
            label.classList.remove('checked-label');
        }
    }, false );
    var inputs = table.getElementsByTagName('input');
    for (var i = 0, len = inputs.length; i < len; i += 1) {
        if (inputs[i].checked) {
            inputs[i].parentNode.classList.add('checked-label');
        }
    }
})(document);