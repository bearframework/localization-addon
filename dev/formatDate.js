var f = function (date, options) {
    var __ = GET_TEXT_FUNCTION_TO_REPLACE;
    var locale = LOCALE_TO_REPLACE;

    var sprintf = function (text, variable) {
        return text.replace('%s', variable);
    };

    var dateObject = new Date(date);
    var currentDateObject = new Date();
    var timestamp = Math.floor(dateObject.getTime() / 1000);
    var currentTimestamp = Math.floor(currentDateObject.getTime() / 1000);

    if (typeof options === 'undefined') {
        options = ['dateAutoYear'];
    }

    var result = {};

    var hasOption = function (name) {
        return options.indexOf(name) !== -1;
    };

    var hasDateOption = hasOption('date');
    var hasDateAutoYearOption = hasOption('dateAutoYear');
    var hasMonthOption = hasOption('month');
    var hasMonthShortOption = hasOption('monthShort');
    var hasYearOption = hasOption('year');
    var hasAutoYearOption = hasOption('autoYear');
    var hasMonthDayOption = hasOption('monthDay');
    var hasWeekDayOption = hasOption('weekDay');
    var hasWeekDayShortOption = hasOption('weekDayShort');
    var hasWeekNumber = hasOption('weekNumber');
    var hasTimeOption = hasOption('time');
    var hasSecondsOption = hasOption('seconds');
    var hasTimeAgoOption = hasOption('timeAgo');

    if (hasTimeAgoOption) {
        var secondsAgo = currentTimestamp - timestamp;
        if (secondsAgo < 60) {
            result.timeAgo = __('bearframework-localization-addon.moment_ago');
        } else if (secondsAgo < 60 * 60) {
            var minutesAgo = Math.floor(secondsAgo / 60);
            result.timeAgo = minutesAgo > 1 ? sprintf(__('bearframework-localization-addon.minutesAgo_ago'), minutesAgo) : __('bearframework-localization-addon.minute_ago');
        } else if (secondsAgo < 60 * 60 * 24) {
            var hoursAgo = Math.floor(secondsAgo / (60 * 60));
            result.timeAgo = hoursAgo > 1 ? sprintf(__('bearframework-localization-addon.hours_ago'), hoursAgo) : __('bearframework-localization-addon.hour_ago');
        } else {
            hasDateAutoYearOption = true;
        }
    }

    if (hasDateOption || hasDateAutoYearOption || hasMonthDayOption) {
        result.monthDay = dateObject.getDate();
    }

    if (hasDateOption || hasDateAutoYearOption || hasMonthOption) {
        result.month = __('bearframework-localization-addon.month_' + (dateObject.getMonth() + 1));
    }

    if (hasMonthShortOption) {
        result.month = __('bearframework-localization-addon.month_' + (dateObject.getMonth() + 1) + '_short');
    }

    if (hasDateOption || hasYearOption || hasDateAutoYearOption || hasAutoYearOption) {
        var year = dateObject.getFullYear();
        if ((hasDateAutoYearOption || hasAutoYearOption) && year === currentDateObject.getFullYear()) {
            // skip
        } else {
            if (locale === 'bg') {
                year += 'г.';
            }
            result.year = year;
        }
    }

    if (hasWeekDayOption) {
        var day = dateObject.getDay();
        if (day === 0) {
            day = 7;
        }
        result.weekDay = __('bearframework-localization-addon.day_' + day);
    }

    if (hasWeekDayShortOption) {
        var day = dateObject.getDay();
        if (day === 0) {
            day = 7;
        }
        result.weekDay = __('bearframework-localization-addon.day_' + day + '_short');
    }

    if (hasWeekNumber) {
        var tempDate = new Date(dateObject);
        tempDate.setHours(0, 0, 0, 0);
        tempDate.setDate(tempDate.getDate() + 3 - (tempDate.getDay() + 6) % 7);
        var week1 = new Date(tempDate.getFullYear(), 0, 4);
        result.weekNumber = (1 + Math.round(((tempDate.getTime() - week1.getTime()) / 86400000 - 3 + (week1.getDay() + 6) % 7) / 7)).toString();
    }

    if (hasTimeOption || hasSecondsOption) {
        if (locale === 'bg') {
            result.time = dateObject.getHours() + ':' + dateObject.getMinutes().toString().padStart(2, '0') + (hasSecondsOption ? ':' + dateObject.getSeconds().toString().padStart(2, '0') : '') + 'ч.';
        } else {
            result.time = dateObject.getHours() + ':' + dateObject.getMinutes().toString().padStart(2, '0') + (hasSecondsOption ? ':' + dateObject.getSeconds().toString().padStart(2, '0') : '');
        }
    }

    var templates = [];
    if (['bg', 'ru'].indexOf(locale) !== -1) {
        templates.push('{weekDay}, {monthDay} {month} {year}');
        templates.push('{weekDay}, {monthDay} {month}');
        templates.push('{monthDay} {month} {year}');
        templates.push('{monthDay} {month}');
    } else {
        templates.push('{weekDay}, {month} {monthDay}, {year}');
        templates.push('{weekDay}, {month} {monthDay}');
        templates.push('{month} {monthDay}, {year}');
        templates.push('{month} {monthDay}');
    }

    var replacedTemplate = '';
    var resultKeys = Object.keys(result);
    for (var i = 0; i < templates.length; i++) {
        var template = templates[i];
        var matches = template.match(/\{.*?\}/g);
        if (matches !== null && matches.length > 0) {
            var keys = matches.map(function (match) {
                return match.replace(/{|}/g, '');
            });
            var intersection = keys.filter(function (key) {
                return resultKeys.indexOf(key) !== -1;
            });
            if (intersection.length === keys.length) {
                replacedTemplate = template;
                for (var j = 0; j < keys.length; j++) {
                    replacedTemplate = replacedTemplate.replace('{' + keys[j] + '}', result[keys[j]]);
                    delete result[keys[j]];
                }
                break;
            }
        }
    }

    var resultValues = Object.values(result);
    if (resultValues.length > 0) {
        if (replacedTemplate !== '') {
            replacedTemplate += ',';
        }
        replacedTemplate += ' ' + resultValues.join(', ');
        replacedTemplate = replacedTemplate.trim();
    }

    return replacedTemplate;
};