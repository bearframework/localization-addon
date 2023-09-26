var f = function (value, options) {

    if (value === null || value === '') {
        return '';
    }

    if (typeof options === 'undefined') {
        options = ['auto', 'autoRound'];
    }

    var bytes = null;
    if (typeof value === 'number') {
        bytes = value;
    } else {
        value = value.replace(',', '.').toLowerCase();
        if (value.match(/^-?\d+$/) !== null || value.match(/^\d+\.\d+$/) !== null) {
            bytes = parseFloat(value);
        } else {
            var suffix = value.substring(value.length - 2);
            var number = null;
            if (['tb', 'gb', 'mb', 'kb'].indexOf(suffix) !== -1) {
                number = parseFloat(value.substring(0, value.length - 2));
            } else {
                suffix = value.substring(value.length - 1);
                if (['t', 'g', 'm', 'k'].indexOf(suffix) !== -1) {
                    number = parseFloat(value.substring(0, value.length - 1));
                }
            }
            if (number !== null) {
                switch (suffix) {
                    case 'tb':
                    case 't':
                        bytes = number * 1024 * 1024 * 1024 * 1024;
                        break;
                    case 'gb':
                    case 'g':
                        bytes = number * 1024 * 1024 * 1024;
                        break;
                    case 'mb':
                    case 'm':
                        bytes = number * 1024 * 1024;
                        break;
                    case 'kb':
                    case 'k':
                        bytes = number * 1024;
                        break;
                }
            }
        }
    }

    var hasOption = function (name) {
        return options.indexOf(name) !== -1;
    };

    if (hasOption('bytes')) {
        return bytes;
    }

    var hasAutoRound = hasOption('autoRound');

    var applyAutoRound = function (result) {
        if (hasAutoRound) {
            var length = result.length;
            for (var i = 0; i < length; i++) {
                if (['0', '.'].indexOf(result.substring(result.length - 1)) !== -1) {
                    result = result.substring(0, result.length - 1);
                }
            }
        }
        return result;
    };

    var round = hasOption('round');

    if ((hasOption('auto') && bytes >= 1073741824) || hasOption('gb')) {
        return applyAutoRound((bytes / 1073741824).toFixed(round ? 0 : 2).toString()) + ' GB';
    }
    if ((hasOption('auto') && bytes >= 1048576) || hasOption('mb')) {
        return applyAutoRound((bytes / 1048576).toFixed(round ? 0 : 2).toString()) + ' MB';
    }
    if ((hasOption('auto') && bytes >= 1024) || hasOption('kb')) {
        return applyAutoRound((bytes / 1024).toFixed(round ? 0 : 2).toString()) + ' KB';
    }
    if ((hasOption('auto') && bytes > 1) || (hasOption('b') && bytes > 1)) {
        return bytes + ' bytes';
    }
    if ((hasOption('auto') && bytes === 1) || hasOption('b')) {
        return bytes + ' byte';
    }
    return '0 bytes';
};