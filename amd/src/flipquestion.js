define(function() {
    return {
        init: function(qaid) {
            var element = document.getElementById('qflashcard-flipcontainer-'.concat(qaid));
            var button = document.getElementById('qflashcard-flipbutton-'.concat(qaid));
            button.onclick = function(e) {
                e.preventDefault();
                element.classList.toggle("flipped");
            };
        }
    };
});
