define(['jquery'], function($) {
    return {
        init: function () {
            $.qtype_flashcard_toggle_flipped = function (qaid) {
                var element = document.getElementById('qflashcard-flipcontainer-'.concat(qaid));
                element.classList.toggle("flipped");
            };
        }
    };
});
