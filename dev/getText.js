var f = function (id) {
    var texts = TEXTS_OBJECT_VALUE_TO_REPLACE;
    return typeof texts[id] !== 'undefined' ? texts[id] : null;
};