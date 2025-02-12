define([
    'jquery',
    'jquery.tipsy',
    'jquery.json',
    'jquery.scrollto',
    'uniform/libs/jquery.placeholder',
    'uniform/libs/jquery-ui-timepicker-addon',
    // 'bootstrap',
    'jquery.ui'],
    function ($) {
        var JSNUniformFormView = function (params) {
            ;
            this.params = params;
            this.lang = params.language;
            this.settings = params.settings;
            this.JSN_UNIFORM_CAPTCHA_PUBLICKEY = params.JSN_UNIFORM_CAPTCHA_PUBLICKEY;
            var formname = this.settings.formName ? this.settings.formName : '';
            this.init(formname);
        }
        var forms = [];
        JSNUniformFormView.prototype = {
            init:function (formname) {
                var self = this;
                $(".form-captcha").hide();
                $('.icon-question-sign').tipsy({
                    gravity:'w',
                    fade:true
                });
                $('form[name=form_' + formname + '] input,form[name=form_' + formname + '] button.btn,form[name=form_' + formname + '] textarea,form[name=form_' + formname + '] select').focus(function () {
                    var form = $(this).parents('form:first');
                    $(form).find(".ui-state-highlight").removeClass("ui-state-highlight");
                    $(this).parents(".control-group").addClass("ui-state-highlight");
                    self.captcha(form);
                }).click(function (e) {
                        var form = $(this).parents('form:first');
                        $(form).find(".ui-state-highlight").removeClass("ui-state-highlight");
                        $(this).parents(".control-group").addClass("ui-state-highlight");
                        e.stopPropagation();
                    });
                $(document).click(function () {
                    $(".ui-state-highlight").removeClass("ui-state-highlight");
                });
                var formDefaultCaptcha = $('.form-captcha')[0];
                if ($(formDefaultCaptcha).size()) {
                    $($(formDefaultCaptcha).parents('form:first').find("input,textarea,select")[0]).focus();
                }
                var randomizeListGroups = $('form[name=form_' + formname + '] select.list');
                randomizeListGroups.each(function () {
                    if ($(this).hasClass("list-randomize")) {
                        self.randomValueItems(this);
                    }
                });
                var randomizeDropdownGroups = $('form[name=form_' + formname + '] select.dropdown');
                randomizeDropdownGroups.each(function () {
                    var selfDropdown = this;
                    if ($(this).hasClass("dropdown-randomize")) {
                        self.randomValueItems(this);
                        $(this).find("option").each(function () {
                            if ($(this).attr("selectdefault") == "true") {
                                $(this).prop("selected", true);
                            }
                        });
                    }
                    $(this).change(function () {
                        if ($(this).val() == "Others" || $(this).val() == "others") {
                            $(selfDropdown).parent().find("textarea.jsn-dropdown-Others").removeClass("hide");
                        } else {
                            $(selfDropdown).parent().find("textarea.jsn-dropdown-Others").addClass("hide");
                        }
                    });
                });
                var randomizeChoiceGroups = $('form[name=form_' + formname + '] div.choices');
                randomizeChoiceGroups.each(function () {
                    var selfChoices = this;
                    if ($(this).hasClass("choices-randomize")) {
                        self.randomValueItems(this);
                    }
                    $(this).find("input[type=radio]").click(function () {
                        if ($(this).val() == "Others" || $(this).val() == "others") {
                            $(selfChoices).find("textarea.jsn-value-Others").removeAttr("disabled");
                        } else {
                            $(selfChoices).find("textarea.jsn-value-Others").attr("disabled", "true");
                        }
                    });
                });
                var randomizeCheckboxGroups = $('form[name=form_' + formname + '] div.checkboxes');
                randomizeCheckboxGroups.each(function () {
                    var selfChexbox = this;
                    if ($(this).hasClass("checkbox-randomize")) {
                        self.randomValueItems(this);
                    }
                    $(this).find(".lbl-allowOther input[type=checkbox]").click(function () {
                        if ($(this).is(':checked')) {
                            $(selfChexbox).find("textarea.jsn-value-Others").removeAttr("disabled");
                        } else {
                            $(selfChexbox).find("textarea.jsn-value-Others").attr("disabled", "true");
                        }
                    });
                });
                $("form[name=form_" + formname + "]").find("input.number,input.phone,input.currency").each(function () {
                    $(this).keypress(function (e) {
                        if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                            return false;
                        }
                    });
                });
                $("form[name=form_" + formname + "]").find("div.choices .jsn-column-item input").change(function () {
                    if ($(this).is(':checked')) {
                        var idField = $(this).parents(".jsn-columns-container").attr("id");
                        $("form[name=form_" + formname + "]").find("div.control-group." + idField).removeAttr("style");
                        self.getActionField(formname, $(this), idField);
                    }
                }).trigger("change");
                $("form[name=form_" + formname + "]").find("div.checkboxes .jsn-column-item input").change(function () {
                    var idField = $(this).parents(".jsn-columns-container").attr("id");
                    $("form[name=form_" + formname + "]").find("div.control-group." + idField).removeAttr("style");
                    $(this).parents(".jsn-columns-container").find("input").each(function () {
                        if ($(this).is(':checked')) {
                            self.getActionField(formname, $(this), idField);
                        }
                    });
                }).trigger("change");
                $("form[name=form_" + formname + "]").find("select.dropdown").change(function () {
                    var idField = $(this).attr("id");
                    $("form[name=form_" + formname + "]").find("div.control-group." + idField).removeAttr("style");
                    self.getActionField(formname, $(this), idField);
                }).trigger("change");
                $("form[name=form_" + formname + "]").find("input.limit-required,textarea.limit-required").each(function () {
                    var settings = $(this).attr("data-limit");
                    var limitSettings = $.evalJSON(settings);
                    if (limitSettings) {
                        $(this).keypress(function (e) {
                                s
                                if (e.which != 27 && e.which != 13 && e.which != 8) {
                                    if (limitSettings.limitType == "Characters" && $(this).val().length == limitSettings.limitMax) {
                                        alert(self.lang['JSN_UNIFORM_CONFIRM_FIELD_MAX_LENGTH'] + " " + limitSettings.limitMax + " Characters");
                                        return false;
                                    }
                                    if (limitSettings.limitType == "Words") {
                                        var lengthValue = $.trim($(this).val()).split(/[\s]+/);
                                        if (lengthValue.length == limitSettings.limitMax && e.which != 0) {
                                            alert(self.lang['JSN_UNIFORM_CONFIRM_FIELD_MAX_LENGTH'] + " " + limitSettings.limitMax + " Words");
                                            return false;
                                        }
                                    }
                                }
                            }
                        );
                    }
                });
                $("form[name=form_" + formname + "] input,textarea").bind('change', function () {
                    self.checkValidateForm($(this).parents(".control-group"), "detailInput", $(this));
                });
                $("form[name=form_" + formname + "]").submit(function () {
                    $(this).find(".help-block").remove();
                    var selfsubmit = this;
                    if (self.checkValidateForm($(this))) {
                        $("#jsn-form-target").remove();
                        $(selfsubmit).find('.message-uniform').html("");
                        var iframe = $('<iframe/>', {
                            name:'jsn-form-target',
                            id:'jsn-form-target'
                        });
                        iframe.appendTo($('body'));
                        iframe.css({
                            display:'none'
                        });
                        var publickey = $(this).find(".form-captcha").attr("publickey");
                        iframe.load(function () {
                            var htmlReturn = iframe.contents().find("body").html();
                            var redirect = iframe.contents().find(".src-redirect").html();
                            var messages = '';
                            if (redirect) {
                                window.location = redirect;
                            }
                            if (htmlReturn) {

                                messages = $.evalJSON(htmlReturn);
                                if (messages.error) {
                                    self.callMessageError(formname, messages.error);
                                } else {
                                    $.ajax({
                                        type:"GET",
                                        async:true,
                                        url:"index.php?option=com_uniform&view=form&task=form.getHtmlForm&tmpl=component&form_id=" + $(selfsubmit).find("input[name=form_id]").val(),
                                        success:function (htmlForm) {
                                            $(selfsubmit).find(".jsn-form-container").html(htmlForm);
                                            if (messages.message) {
                                                $(selfsubmit).find('.message-uniform').html(
                                                    $("<div/>", {
                                                        "class":"success-uniform alert alert-success"
                                                    }).append(
                                                        $("<button/>", {
                                                            "class":"close",
                                                            "onclick":"return false;",
                                                            "data-dismiss":"alert",
                                                            text:"×"
                                                        })).append(messages.message));
                                            }
                                            self.init(formname);
                                            var messagesFocus = $("form[name=form_" + formname + "]").find(".message-uniform")[0];
                                            $(window).scrollTop($(messagesFocus).offset().top - 50);
                                        }
                                    });
                                }
                            } else {
                                $.ajax({
                                    type:"GET",
                                    async:true,
                                    url:"index.php?option=com_uniform&view=form&task=form.getHtmlForm&tmpl=component&form_id=" + $(selfsubmit).find("input[name=form_id]").val(),
                                    success:function (htmlForm) {
                                        $(selfsubmit).find(".jsn-form-container").html(htmlForm);
                                        self.init(formname);
                                        var messagesFocus = $("form[name=form_" + formname + "]").find(".message-uniform")[0];
                                        $(window).scrollTop($(messagesFocus).offset().top - 50);
                                    }
                                });
                            }
                            var idcaptcha;
                            idcaptcha = $(selfsubmit).find(".form-captcha").attr("id");
                            if (idcaptcha) {
                                Recaptcha.destroy();
                                Recaptcha.create(publickey, idcaptcha, {
                                    theme:'white',
                                    tabindex:0,
                                    callback:function () {
                                        $(selfsubmit).find(".form-captcha").removeClass("error");
                                        $(selfsubmit).find(".form-captcha #recaptcha_area").addClass("controls");
                                        if (messages) {
                                            if (messages.error.captcha) {
                                                $(selfsubmit).find(".form-captcha").addClass("error");
                                                $(selfsubmit).find(".form-captcha #recaptcha_area").append(
                                                    $("<span/>", {
                                                        "class":"help-block"
                                                    }).append(
                                                        $("<span/>", {
                                                            "class":"validation-result label label-important",
                                                            text:messages.error.captcha
                                                        })));
                                                $(selfsubmit).find("#recaptcha_response_field").focus();
                                            }
                                        }
                                    }
                                });
                            }
                        });
                        $(this).attr('target', 'jsn-form-target');
                    } else {
                        return false;
                    }
                });
                $("form[name=form_" + formname + "]").find("input.jsn-daterangepicker").each(function () {
                    var dateSettings = $.evalJSON($(this).attr("date-settings"));
                    if (dateSettings) {
                        var yearRangeList = [];
                        if (dateSettings.yearRangeMin && dateSettings.yearRangeMax) {
                            yearRangeList.push(dateSettings.yearRangeMin);
                            yearRangeList.push(dateSettings.yearRangeMax);
                        } else if (dateSettings.yearRangeMin) {
                            yearRangeList.push(dateSettings.yearRangeMin);
                            yearRangeList.push((new Date).getFullYear());
                        } else if (dateSettings.yearRangeMax) {
                            yearRangeList.push(dateSettings.yearRangeMax);
                            yearRangeList.push((new Date).getFullYear());
                        }
                        var yearRange = "1930:+0";
                        if (yearRangeList.length) {
                            yearRange = yearRangeList.join(":");
                        }
                        var dateOptionFormat = "";
                        if (dateSettings.dateOptionFormat == "custom") {
                            dateOptionFormat = dateSettings.customFormatDate;
                        } else {
                            dateOptionFormat = dateSettings.dateOptionFormat;
                        }
                        if (dateSettings.dateFormat == "1" && dateSettings.timeFormat == "1") {
                            $(this).datetimepicker({
                                changeMonth:true,
                                changeYear:true,
                                showOn:"button",
                                yearRange:yearRange,
                                dateFormat:dateOptionFormat,
                                timeFormat:dateSettings.timeOptionFormat,
                                timeText:"",
                                hourText:self.lang['JSN_UNIFORM_DATE_HOUR_TEXT'],
                                minuteText:self.lang['JSN_UNIFORM_DATE_MINUTE_TEXT'],
                                closeText:self.lang['JSN_UNIFORM_DATE_CLOSE_TEXT'],
                                prevText:self.lang['JSN_UNIFORM_DATE_PREV_TEXT'],
                                nextText:self.lang['JSN_UNIFORM_DATE_NEXT_TEXT'],
                                currentText:self.lang['JSN_UNIFORM_DATE_CURRENT_TEXT'],
                                monthNames:[self.lang['JSN_UNIFORM_DATE_MONTH_JANUARY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MARCH'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_APRIL'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MAY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JUNE'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JULY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_AUGUST'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_OCTOBER'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_DECEMBER']],
                                monthNamesShort:[self.lang['JSN_UNIFORM_DATE_MONTH_JANUARY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MARCH_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_APRIL_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JUNE_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JULY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_AUGUST_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_OCTOBER_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_DECEMBER_SHORT']],
                                dayNames:[self.lang['JSN_UNIFORM_DATE_DAY_SUNDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_MONDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_TUESDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_THURSDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_FRIDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_SATURDAY']],
                                dayNamesShort:[self.lang['JSN_UNIFORM_DATE_DAY_SUNDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_MONDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_TUESDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_THURSDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_FRIDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_SATURDAY_SHORT']],
                                dayNamesMin:[self.lang['JSN_UNIFORM_DATE_DAY_SUNDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_MONDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_TUESDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_THURSDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_FRIDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_SATURDAY_MIN']],
                                weekHeader:self.lang['JSN_UNIFORM_DATE_DAY_WEEK_HEADER']
                            });
                        } else if (dateSettings.dateFormat == "1") {
                            $(this).datepicker({
                                changeMonth:true,
                                changeYear:true,
                                showOn:"button",
                                yearRange:yearRange,
                                dateFormat:dateOptionFormat,
                                hourText:self.lang['JSN_UNIFORM_DATE_HOUR_TEXT'],
                                minuteText:self.lang['JSN_UNIFORM_DATE_MINUTE_TEXT'],
                                closeText:self.lang['JSN_UNIFORM_DATE_CLOSE_TEXT'],
                                prevText:self.lang['JSN_UNIFORM_DATE_PREV_TEXT'],
                                nextText:self.lang['JSN_UNIFORM_DATE_NEXT_TEXT'],
                                currentText:self.lang['JSN_UNIFORM_DATE_CURRENT_TEXT'],
                                monthNames:[self.lang['JSN_UNIFORM_DATE_MONTH_JANUARY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MARCH'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_APRIL'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MAY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JUNE'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JULY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_AUGUST'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_OCTOBER'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_DECEMBER']],
                                monthNamesShort:[self.lang['JSN_UNIFORM_DATE_MONTH_JANUARY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MARCH_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_APRIL_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JUNE_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JULY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_AUGUST_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_OCTOBER_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_DECEMBER_SHORT']],
                                dayNames:[self.lang['JSN_UNIFORM_DATE_DAY_SUNDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_MONDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_TUESDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_THURSDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_FRIDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_SATURDAY']],
                                dayNamesShort:[self.lang['JSN_UNIFORM_DATE_DAY_SUNDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_MONDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_TUESDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_THURSDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_FRIDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_SATURDAY_SHORT']],
                                dayNamesMin:[self.lang['JSN_UNIFORM_DATE_DAY_SUNDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_MONDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_TUESDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_THURSDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_FRIDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_SATURDAY_MIN']],
                                weekHeader:self.lang['JSN_UNIFORM_DATE_DAY_WEEK_HEADER']
                            });
                        } else if (dateSettings.timeFormat == "1") {
                            $(this).timepicker({
                                showOn:"button",
                                timeText:"",
                                timeFormat:dateSettings.timeOptionFormat,
                                hourText:self.lang['JSN_UNIFORM_DATE_HOUR_TEXT'],
                                minuteText:self.lang['JSN_UNIFORM_DATE_MINUTE_TEXT'],
                                closeText:self.lang['JSN_UNIFORM_DATE_CLOSE_TEXT'],
                                prevText:self.lang['JSN_UNIFORM_DATE_PREV_TEXT'],
                                nextText:self.lang['JSN_UNIFORM_DATE_NEXT_TEXT'],
                                currentText:self.lang['JSN_UNIFORM_DATE_CURRENT_TEXT'],
                                monthNames:[self.lang['JSN_UNIFORM_DATE_MONTH_JANUARY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MARCH'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_APRIL'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MAY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JUNE'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JULY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_AUGUST'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_OCTOBER'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_DECEMBER']],
                                monthNamesShort:[self.lang['JSN_UNIFORM_DATE_MONTH_JANUARY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MARCH_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_APRIL_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JUNE_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JULY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_AUGUST_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_OCTOBER_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_DECEMBER_SHORT']],
                                dayNames:[self.lang['JSN_UNIFORM_DATE_DAY_SUNDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_MONDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_TUESDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_THURSDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_FRIDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_SATURDAY']],
                                dayNamesShort:[self.lang['JSN_UNIFORM_DATE_DAY_SUNDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_MONDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_TUESDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_THURSDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_FRIDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_SATURDAY_SHORT']],
                                dayNamesMin:[self.lang['JSN_UNIFORM_DATE_DAY_SUNDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_MONDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_TUESDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_THURSDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_FRIDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_SATURDAY_MIN']],
                                weekHeader:self.lang['JSN_UNIFORM_DATE_DAY_WEEK_HEADER']
                            });
                        } else {
                            $(this).datepicker({
                                changeMonth:true,
                                changeYear:true,
                                yearRange:yearRange,
                                showOn:"button",
                                hourText:self.lang['JSN_UNIFORM_DATE_HOUR_TEXT'],
                                minuteText:self.lang['JSN_UNIFORM_DATE_MINUTE_TEXT'],
                                closeText:self.lang['JSN_UNIFORM_DATE_CLOSE_TEXT'],
                                prevText:self.lang['JSN_UNIFORM_DATE_PREV_TEXT'],
                                nextText:self.lang['JSN_UNIFORM_DATE_NEXT_TEXT'],
                                currentText:self.lang['JSN_UNIFORM_DATE_CURRENT_TEXT'],
                                monthNames:[self.lang['JSN_UNIFORM_DATE_MONTH_JANUARY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MARCH'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_APRIL'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MAY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JUNE'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JULY'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_AUGUST'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_OCTOBER'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_DECEMBER']],
                                monthNamesShort:[self.lang['JSN_UNIFORM_DATE_MONTH_JANUARY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MARCH_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_APRIL_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_MAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JUNE_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_JULY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_AUGUST_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_OCTOBER_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_MONTH_DECEMBER_SHORT']],
                                dayNames:[self.lang['JSN_UNIFORM_DATE_DAY_SUNDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_MONDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_TUESDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_THURSDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_FRIDAY'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_SATURDAY']],
                                dayNamesShort:[self.lang['JSN_UNIFORM_DATE_DAY_SUNDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_MONDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_TUESDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_THURSDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_FRIDAY_SHORT'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_SATURDAY_SHORT']],
                                dayNamesMin:[self.lang['JSN_UNIFORM_DATE_DAY_SUNDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_MONDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_TUESDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_THURSDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_FRIDAY_MIN'],
                                    self.lang['JSN_UNIFORM_DATE_DAY_SATURDAY_MIN']],
                                weekHeader:self.lang['JSN_UNIFORM_DATE_DAY_WEEK_HEADER']
                            });
                        }
                        $("button.ui-datepicker-trigger").addClass("btn btn-icon").html($("<i/>", {
                            "class":"icon-calendar"
                        }));
                    }
                });
                $("form[name=form_" + formname + "] .form-actions .prev").click(function () {
                    $('form[name=form_' + formname + '] div.jsn-form-content').each(function (i) {
                        if (!$(this).is(':hidden')) {
                            if (self.checkValidateForm($(this))) {
                                self.prevpaginationPage(formname);
                            }
                            return false;

                        }
                    });
                });
                $("form[name=form_" + formname + "] .form-actions .next").click(function () {
                    $('form[name=form_' + formname + '] div.jsn-form-content').each(function (i) {
                        if (!$(this).is(':hidden')) {
                            if (self.checkValidateForm($(this))) {
                                self.nextpaginationPage(formname);
                            }
                            return false;

                        }
                    });
                });
                this.defaultPage(formname);
                $('input, textarea').placeholder();
            },
            getActionField:function (formname, selfInput, idField) {
                var dataSettings = $(selfInput).parents(".control-group").attr("data-settings");
                if (dataSettings) {
                    dataSettings = $.evalJSON(dataSettings);
                }
                if (dataSettings) {
                    $.each(dataSettings, function (i, item) {
                        if ($(selfInput).val() == i) {
                            if (item.showField) {
                                var classShow = [];
                                $.each(item.showField, function (j, actionField) {
                                    classShow.push(".control-group." + actionField);
                                });
                                $("form[name=form_" + formname + "]").find(classShow.join(",")).addClass(idField).show();
                            }
                            if (item.hideField) {
                                var classHide = [];
                                $.each(item.hideField, function (j, actionField) {
                                    classHide.push("div.control-group." + actionField);
                                });
                                $("form[name=form_" + formname + "]").find(classHide.join(",")).addClass(idField).hide();
                            }
                        }
                    });
                }
            },
            randomValueItems:function (_this) {
                var group = $(_this),
                    choices = group.find('.jsn-column-item'),
                    otherItem = choices.filter(function () {
                        return $('label.lbl-allowOther', this).size() > 0;
                    }),
                    randomItems = choices.not(otherItem);
                randomItems.detach();
                otherItem.detach();
                while (randomItems.length > 0) {
                    var index = Math.floor(Math.random() * choices.length),
                        choice = randomItems[index];

                    if (group.find(".lbl-allowOther").size()) {
                        group.find(".lbl-allowOther").before(choice);
                    } else {
                        group.append(choice);
                    }
                    delete(randomItems[index]);
                    var newList = [];
                    $(randomItems).each(function (index, element) {
                        if (element !== undefined) {
                            newList.push(element);
                        }
                    });
                    randomItems = newList;
                }
                delete(randomItems[0]);
                if (group.find(".lbl-allowOther").size()) {
                    group.find(".lbl-allowOther").before(otherItem);
                } else {
                    group.append(otherItem);
                }
                return true;
            },
            captcha:function (form) {
                var self = this;
                var idcaptcha = "";
                var idcaptcha = form.find(".form-captcha").attr("id");
                var publickey = form.find(".form-captcha").attr("publickey");
                if (form.find(".form-captcha").length > 0 && form.find(".form-captcha").is(':hidden') && idcaptcha) {
                    $(".form-captcha").hide();
                    form.find(".form-captcha").show();
                    Recaptcha.create(publickey, idcaptcha, {
                        theme:'white',
                        tabindex:0,
                        callback:function () {
                            $(form).find(".form-captcha").removeClass("error");
                            $(form).find(".form-captcha #recaptcha_area").addClass("controls");
                        }
                    });
                }
            },
            callMessageError:function (formname, messageError) {
                var self = this;
                $.each(messageError, function (key, value) {
                    if (key != "captcha") {
                        if (key == "name" || key == "address" || key == "date" || key == "phone" || key == "currency") {
                            $.each(value, function (i, item) {
                                $("form[name=form_" + formname + "] input[name=currency\\[" + i + "\\]\\[value\\]],form[name=form_" + formname + "] input[name=phone\\[" + i + "\\]\\[default\\]],form[name=form_" + formname + "] input[name=phone\\[" + i + "\\]\\[one\\]],form[name=form_" + formname + "] input[name=date\\[" + i + "\\]\\[date\\]],form[name=form_" + formname + "] input[name=name\\[" + i + "\\]\\[first\\]],form[name=form_" + formname + "] input[name=address\\[" + i + "\\]\\[street\\]]").parents(".control-group").addClass("error").find(".controls").append($("<span/>", {
                                    "class":"help-block"
                                }).append(
                                    $("<span/>", {
                                        "class":"validation-result label label-important",
                                        text:item
                                    })));
                            });
                        } else if (key != "max-upload") {
                            if (key == "captcha_2") {
                                $("form[name=form_" + formname + "] #jsn-captcha").parents(".control-group").addClass("error").find(".controls").append($("<span/>", {
                                    "class":"help-block"
                                }).append(
                                    $("<span/>", {
                                        "class":"validation-result label label-important",
                                        text:value
                                    })));
                            } else {
                                if ($("form[name=form_" + formname + "] #" + key).size()) {
                                    $("form[name=form_" + formname + "] #" + key).parents(".control-group").addClass("error").find(".controls").append($("<span/>", {
                                        "class":"help-block"
                                    }).append(
                                        $("<span/>", {
                                            "class":"validation-result label label-important",
                                            text:value
                                        })));
                                }
                            }
                        } else if (key == "max-upload") {
                            $("form[name=form_" + formname + "] .message-uniform").html($("<div/>", {
                                "class":"alert alert-error"
                            }).append(value));
                        }
                    }
                });
                var formError = $('form[name=form_' + formname + '] .error')[0];

                if ($(formError).parents('.jsn-form-content').attr("data-value")) {
                    $('form[name=form_' + formname + '] .jsn-form-content').hide();
                    $(formError).parents('.jsn-form-content').show();
                    self.checkPage(formname);

                } else {
                    var countPage = $('form[name=form_' + formname + '] div.jsn-form-content').length;
                    $('form[name=form_' + formname + '] div.jsn-form-content')[countPage - 1].show();
                    $('form[name=form_' + formname + '] input,form[name=form_' + formname + '] button,form[name=form_' + formname + '] textarea').focus();
                }
                if ($("form[name=form_" + formname + "]").find(".error input,.error textarea,.error select").length) {
                    var fieldFocus = $("form[name=form_" + formname + "]").find(".error")[0];
                    if ($(fieldFocus).find(".blank-required").size()) {
                        $(fieldFocus).find("input,select,textarea").each(function () {
                            var val = $(this).val();
                            var val2 = val.replace(' ', '');
                            if (val2 == '' || val2 == 0) {
                                $(window).scrollTop($(this).offset().top - 50);
                                $(this).click();
                                return false;
                            }
                        })
                    } else {
                        var fieldFocus = $("form[name=form_" + formname + "]").find(".error input,.error textarea,.error select")[0];
                        $(window).scrollTop($(fieldFocus).offset().top - 50);
                        fieldFocus.click();
                    }
                }

            },
            defaultPage:function (formname) {
                if (forms.length < 1) {
                    this.captcha($('form[name=form_' + formname + ']'));
                }
                $($('form[name=form_' + formname + '] div.jsn-form-content')[0]).removeClass("hide");
                this.checkPage(formname);
                $('form[name=form_' + formname + ']').find("#page-loading").addClass("hide");
                forms.push(formname);
            },
            checkPage:function (formname) {
                $('form[name=form_' + formname + '] div.jsn-form-content').each(function (i) {
                    if (!$(this).hasClass("hide")) {
                        if ($(this).next().attr("data-value")) {
                            $("form[name=form_" + formname + "] .form-actions .next").removeClass("hide");
                        } else {
                            $("form[name=form_" + formname + "] .form-actions .next").addClass("hide");
                        }
                        if ($(this).prev().attr("data-value")) {
                            $("form[name=form_" + formname + "] .form-actions .prev").removeClass("hide");
                        } else {
                            $("form[name=form_" + formname + "] .form-actions .prev").addClass("hide");
                        }
                        if (i + 1 == $('form[name=form_' + formname + '] div.jsn-form-content').length) {
                            $("form[name=form_" + formname + "] .form-actions .next").addClass("hide");
                            $("form[name=form_" + formname + "] .form-actions .jsn-form-submit").removeClass("hide");

                        } else {
                            $("form[name=form_" + formname + "] .form-actions .next").removeClass("hide");
                            $("form[name=form_" + formname + "] .form-actions .jsn-form-submit").addClass("hide");
                        }
                    }
                });
            },
            nextpaginationPage:function (formname) {
                var self = this;
                $('form[name=form_' + formname + '] div.jsn-form-content').each(function () {
                    if (!$(this).hasClass("hide")) {
                        $(this).addClass("hide");
                        $(this).next().removeClass("hide");
                        self.checkPage(formname);
                        return false;
                    }
                });
            },
            prevpaginationPage:function (formname) {
                var self = this;
                $('form[name=form_' + formname + '] div.jsn-form-content').each(function () {
                    if (!$(this).hasClass("hide")) {
                        $(this).addClass("hide");
                        $(this).prev().removeClass("hide");
                        self.checkPage(formname);
                        return false;
                    }
                });
            },
            checkValidateForm:function (_this, type, element) {
                var check = 0;
                var $inputBlank = $(_this).find(".blank-required");
                var self = this;
                $inputBlank.each(function () {
                    var checkBlank = true;
                    $(this).find(".help-blank").remove();
                    $(this).parent().removeClass("error");
                    $(this).find("input,select,textarea").each(function () {
                        var val = $(this).val();
                        var val2 = val.replace(' ', '');
                        if (val2 == '' || val2 == 0) {
                            checkBlank = false;
                        }
                    })
                    if (!checkBlank) {
                        $(this).parent().addClass("error");
                        $(this).append(
                            $("<span/>", {
                                "class":"help-block help-blank"
                            }).append(
                                $("<span/>", {
                                    "class":"validation-result label label-important",
                                    text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_CANNOT_EMPTY']
                                })));
                        check++;
                    }
                });
                var groupBlank = $(_this).find(".group-blank-required");
                groupBlank.each(function () {
                    var checkGroupBlank = false;
                    $(this).find(".help-blank").remove();
                    $(this).parent().removeClass("error");
                    $(this).find("input").each(function () {
                        var val = $(this).val();
                        var val2 = val.replace(' ', '');
                        if (val2 != '') {
                            checkGroupBlank = true;
                        }
                    })
                    if (!checkGroupBlank) {
                        $(this).parent().addClass("error");
                        $(this).append(
                            $("<span/>", {
                                "class":"help-block help-blank"
                            }).append(
                                $("<span/>", {
                                    "class":"validation-result label label-important",
                                    text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_CANNOT_EMPTY']
                                })));
                        check++;
                    }
                });
                var $dropdown = $(_this).find(".dropdown-required");
                $dropdown.each(function () {
                    $(this).find(".help-dropdown").remove();
                    $(this).parent().removeClass("error");
                    if ($(this).find("select").val() == "") {
                        $(this).parent().addClass("error");
                        $(this).append(
                            $("<span/>", {
                                "class":"help-block help-dropdown"
                            }).append(
                                $("<span/>", {
                                    "class":"validation-result label label-important",
                                    text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_CANNOT_EMPTY']
                                })))
                        check++;
                    } else if ($(this).find("select option:selected").hasClass('lbl-allowOther')) {
                        var selfRadio = this;

                        $(this).find(".jsn-dropdown-Others").focusout(function () {
                            var checkRadio = false;
                            var valchoices = $(selfRadio).find(".jsn-dropdown-Others").val();
                            var valchoices2 = valchoices.replace(' ', '');
                            if (valchoices2 == '') {
                                checkRadio = true;
                            }
                            if (checkRadio) {
                                $(selfRadio).find(".help-dropdown").remove();
                                $(selfRadio).parent().addClass("error");
                                $(selfRadio).append(
                                    $("<span/>", {
                                        "class":"help-block help-dropdown"
                                    }).append(
                                        $("<span/>", {
                                            "class":"validation-result label label-important",
                                            text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_CANNOT_EMPTY']
                                        })))
                                check++;
                            }
                        });
                        if (type != "detailInput") {
                            $(this).find(".jsn-dropdown-Others").trigger("focusout");
                        }

                    }
                });
                var $inputEmailNull = $(_this).find("input.email");
                $inputEmailNull.each(function () {
                    var parentEmail = $(this).parents(".control-group");
                    $(parentEmail).find(".help-email").remove();
                    $(parentEmail).removeClass("error");
                    var val = $(this).val();
                    var filter = /^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,6})$/;
                    if (!filter.test(val) && $(this).hasClass("email-required")) {
                        $(parentEmail).addClass("error");
                        $(this).parents(".controls").append(
                            $("<span/>", {
                                "class":"help-block help-email"
                            }).append(
                                $("<span/>", {
                                    "class":"validation-result label label-important",
                                    text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_INVALID']
                                })));
                        check++;
                    } else if (!$(this).hasClass("email-required") && val && !filter.test(val)) {
                        $(parentEmail).addClass("error");
                        $(this).parents(".controls").append(
                            $("<span/>", {
                                "class":"help-block help-email"
                            }).append(
                                $("<span/>", {
                                    "class":"validation-result label label-important",
                                    text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_INVALID']
                                })));
                        check++;
                    }
                    if (val && filter.test(val) && $(parentEmail).find(".jsn-email-confirm").hasClass("jsn-email-confirm") && ($(element).hasClass("jsn-email-confirm") || !$(parentEmail).hasClass("ui-state-highlight"))) {
                        if ($(parentEmail).find(".jsn-email-confirm").val() != $(this).val()) {
                            $(parentEmail).addClass("error");
                            $(this).parents(".controls").append(
                                $("<span/>", {
                                    "class":"help-block help-email"
                                }).append(
                                    $("<span/>", {
                                        "class":"validation-result label label-important",
                                        text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_EMAIL_CONFIRM']
                                    })));
                            check++;
                        }
                    }

                });
                var $inputWebsite = $(_this).find("input.website");
                $inputWebsite.each(function () {
                    $(this).parent().find(".help-website").remove();
                    $(this).parent().parent().removeClass("error");
                    var val = $(this).val();
                    var regexp = /^(https?:\/\/|ftp:\/\/|www([0-9]{0,9})?\.)?(((([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;
                    if ((!regexp.test(val) && $(this).hasClass("website-required")) || (val != "" && val != "http://" && val != "https://" && !$(this).hasClass("website-required") && !regexp.test(val))) {
                        $(this).parent().parent().addClass("error");
                        $(this).after(
                            $("<span/>", {
                                "class":"help-block help-website"
                            }).append(
                                $("<span/>", {
                                    "class":"validation-result label label-important",
                                    text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_INVALID']
                                })));
                        check++;
                    }
                });
                var $inputInteger = $(_this).find("input.integer-required");
                $inputInteger.each(function () {
                    $(this).parent().find(".help-integer").remove();
                    $(this).parent().parent().removeClass("error");
                    var val = $(this).val();
                    var regexp = /^[0-9]+$/;
                    if (!regexp.test(val)) {
                        $(this).parent().parent().addClass("error");
                        $(this).parent().append(
                            $("<span/>", {
                                "class":"help-block help-integer"
                            }).append(
                                $("<span/>", {
                                    "class":"validation-result label label-important",
                                    text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_INVALID']
                                })));
                        check++;
                    }
                });
                var $valueNumberLimit = $(_this).find(".number-limit-required");
                $valueNumberLimit.each(function () {
                    var limitNumberSettings = $.evalJSON($(this).attr("data-limit"));
                    $(this).parent().find(".help-limit").remove();
                    $(this).parent().parent().removeClass("error");
                    if ($(this).val() != '' || $(this).val() != 0) {
                        if (parseInt($(this).val(), 10) < limitNumberSettings.limitMin) {
                            check++;
                            $(this).parent().parent().addClass("error");
                            $(this).parent().append(
                                $("<span/>", {
                                    "class":"help-block help-limit"
                                }).append(
                                    $("<span/>", {
                                        "class":"validation-result label label-important",
                                        text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_MIN_NUMBER'] + " " + limitNumberSettings.limitMin
                                    })));
                        } else if (parseInt($(this).val(), 10) > limitNumberSettings.limitMax) {
                            check++;
                            $(this).parent().parent().addClass("error");
                            $(this).parent().append(
                                $("<span/>", {
                                    "class":"help-block help-limit"
                                }).append(
                                    $("<span/>", {
                                        "class":"validation-result label label-important",
                                        text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_MAX_NUMBER'] + " " + limitNumberSettings.limitMax
                                    })));
                        }
                    }
                });
                var $valueLimit = $(_this).find(".limit-required");
                $valueLimit.each(function () {
                    var limitSettings = $.evalJSON($(this).attr("data-limit"));
                    $(this).parent().find(".help-limit").remove();
                    $(this).parent().parent().removeClass("error");
                    if ($(this).val() != '' || $(this).val() != 0) {
                        if (limitSettings.limitType == "Words") {
                            var lengthValue = $.trim($(this).val()).split(/[\s]+/);
                            if (lengthValue.length < limitSettings.limitMin) {
                                check++;
                                $(this).parent().parent().addClass("error");
                                $(this).after(
                                    $("<span/>", {
                                        "class":"help-block help-limit"
                                    }).append(
                                        $("<span/>", {
                                            "class":"validation-result label label-important",
                                            text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_MIN_LENGTH'] + " " + limitSettings.limitMin + " Words"
                                        })));
                            } else if (lengthValue.length > limitSettings.limitMax) {
                                check++;
                                $(this).parent().parent().addClass("error");
                                $(this).after(
                                    $("<span/>", {
                                        "class":"help-block help-limit"
                                    }).append(
                                        $("<span/>", {
                                            "class":"validation-result label label-important",
                                            text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_MAX_LENGTH'] + " " + limitSettings.limitMax + " Words"
                                        })));
                            }
                        } else {
                            if ($(this).val().length < limitSettings.limitMin) {
                                check++;
                                $(this).parent().parent().addClass("error");
                                $(this).after(
                                    $("<span/>", {
                                        "class":"help-block help-limit"
                                    }).append(
                                        $("<span/>", {
                                            "class":"validation-result label label-important",
                                            text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_MIN_LENGTH'] + " " + limitSettings.limitMin + " Character"
                                        })));
                            } else if ($(this).val().length > limitSettings.limitMax) {
                                check++;
                                $(this).parent().parent().addClass("error");
                                $(this).after(
                                    $("<span/>", {
                                        "class":"help-block help-limit"
                                    }).append(
                                        $("<span/>", {
                                            "class":"validation-result label label-important",
                                            text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_MAX_LENGTH'] + " " + limitSettings.limitMax + " Character"
                                        })));
                            }
                        }
                    }
                });
                var $list = $(_this).find(".list-required");
                $list.each(function () {
                    $(this).parent().find(".help-list").remove();
                    $(this).parent().removeClass("error");
                    if (!$(this).find("select").val()) {
                        $(this).parent().addClass("error");
                        $(this).after(
                            $("<span/>", {
                                "class":"help-block help-list"
                            }).append(
                                $("<span/>", {
                                    "class":"validation-result label label-important",
                                    text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_INVALID']
                                })));
                        check++;
                    }

                });
                var $inputchoices = $(_this).find(".choices-required");
                $inputchoices.each(function () {
                    $(this).find(".help-choices").remove();
                    $(this).parent().removeClass("error");
                    if ($(this).find("input[type=radio]:checked").length < 1) {
                        $(this).parent().addClass("error");
                        $(this).append(
                            $("<span/>", {
                                "class":"help-block help-choices"
                            }).append(
                                $("<span/>", {
                                    "class":"validation-result label label-important",
                                    text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_CANNOT_EMPTY']
                                })))
                        check++;
                    } else if ($(this).find("input[type=radio]:checked").hasClass('allowOther') && $(this).find("input[type=radio]:checked").length == 1) {
                        var selfRadio = this;

                        $(this).find(".jsn-value-Others").focusout(function () {
                            var checkRadio = false;
                            var valchoices = $(selfRadio).find(".jsn-value-Others").val();
                            var valchoices2 = valchoices.replace(' ', '');
                            if (valchoices2 == '') {
                                checkRadio = true;
                            }
                            if (checkRadio) {
                                $(selfRadio).find(".help-choices").remove();
                                $(selfRadio).parent().addClass("error");
                                $(selfRadio).append(
                                    $("<span/>", {
                                        "class":"help-block help-choices"
                                    }).append(
                                        $("<span/>", {
                                            "class":"validation-result label label-important",
                                            text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_CANNOT_EMPTY']
                                        })))
                                check++;
                            }
                        });
                        if (type != "detailInput") {
                            $(this).find(".jsn-value-Others").trigger("focusout");
                        }

                    }
                });
                var $inputCheckbox = $(_this).find(".checkbox-required");
                $inputCheckbox.each(function () {
                    $(this).find(".help-checkbox").remove();
                    $(this).parent().parent().removeClass("error");
                    if ($(this).find("input[type=checkbox]:checked").length < 1) {
                        $(this).parent().parent().addClass("error");
                        $(this).append(
                            $("<span/>", {
                                "class":"help-block help-checkbox"
                            }).append(
                                $("<span/>", {
                                    "class":"validation-result label label-important",
                                    text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_CANNOT_EMPTY']
                                })))
                        check++;
                    } else if ($(this).find("input[type=checkbox]:checked").length == 1 && $(this).find("input[type=checkbox]:checked").hasClass('allowOther')) {

                        var selfCheckbox = this;

                        $(this).find(".jsn-value-Others").focusout(function () {
                            var checkCheckbox = false;
                            var valchoices = $(selfCheckbox).find(".jsn-value-Others").val();
                            var valchoices2 = valchoices.replace(' ', '');
                            if (valchoices2 == '') {
                                checkCheckbox = true;
                            }
                            if (checkCheckbox) {
                                $(selfCheckbox).find(".help-checkbox").remove();
                                $(selfCheckbox).parent().parent().addClass("error");
                                $(selfCheckbox).append(
                                    $("<span/>", {
                                        "class":"help-block help-checkbox"
                                    }).append(
                                        $("<span/>", {
                                            "class":"validation-result label label-important",
                                            text:self.lang['JSN_UNIFORM_CONFIRM_FIELD_CANNOT_EMPTY']
                                        })))
                                check++;
                            }
                        });
                        if (type != "detailInput") {
                            $(this).find(".jsn-value-Others").trigger("focusout");
                        }
                    }

                });
                if (check > 0 && type != "detailInput") {
                    var fieldFocus = $(_this).find(".error")[0];
                    if ($(fieldFocus).find(".blank-required").size()) {
                        $(fieldFocus).find("input,select,textarea").each(function () {
                            var val = $(this).val();
                            var val2 = val.replace(' ', '');
                            if (val2 == '' || val2 == 0) {
                                $(window).scrollTop($(this).offset().top - 50);
                                $(this).focus();
                                $(this).click();
                                return false;
                            }
                        })
                    } else {
                        var fieldFocus = $(_this).find(".error input,.error textarea,.error select")[0];
                        $(window).scrollTop($(fieldFocus).offset().top - 50);
                        $(fieldFocus).focus();
                    }
                    return false;
                }
                if (check > 0 && type == "detailInput") {
                    return false;
                }
                return true;
            },
            getBoxStyle:function (element) {

                var style = {
                    width:element.width(),
                    height:element.height(),
                    outerHeight:element.outerHeight(),
                    outerWidth:element.outerWidth(),
                    offset:element.offset(),
                    margin:{
                        left:parseInt(element.css('margin-left')),
                        right:parseInt(element.css('margin-right')),
                        top:parseInt(element.css('margin-top')),
                        bottom:parseInt(element.css('margin-bottom'))
                    },
                    padding:{
                        left:parseInt(element.css('padding-left')),
                        right:parseInt(element.css('padding-right')),
                        top:parseInt(element.css('padding-top')),
                        bottom:parseInt(element.css('padding-bottom'))
                    }
                };

                return style;
            }
        }
        return JSNUniformFormView;
    });