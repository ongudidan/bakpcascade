/**
 * @category Libraries
 * @package Unifom
 * @author JoomlaShine.com
 * @copyright JoomlaShine.com
 * @license GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 * @version $Id: visualdesign.js 19013 2012-11-28 04:48:47Z thailv $
 * @link JoomlaShine.com
 */
define([
    'jquery',
    'uniform/visualdesign/controls',
    'uniform/dialogedition',
    'uniform/visualdesign/itemlist',
    'uniform/libs/jquery.tmpl',
    'uniform/libs/jquery.placeholder',
    'uniform/libs/jquery-ui-timepicker-addon',
    'jquery.jwysiwyg',
    'jquery.json',
    'jquery.tipsy',
    'jquery.ui' ],
    function ($, JSNVisualControls, JSNUniformDialogEdition) {
        /**
         * Constructor of JSNVisualDesign class
         * @param container
         */
        var listLabel = [];
        var checkChangeEmail = false;
        var dataEmailSubmitter = [];
        var edition = "";
        var lang = [];
        var limitSize = '';
        var limitEx = '';

        function JSNVisualDesign(container, params) {
            this.params = params;
            lang = params.language;
            dataEmailSubmitter = params.dataEmailSubmitter;
            edition = params.edition;
            limitSize = params.limitSize;
            limitEx = params.limitEx;
            this.newElement = $('<a href="javascript:void(0);" class="jsn-add-more">' + lang['JSN_UNIFORM_ADD_FIELD'] + '</a>');
            this.container;
            this.init(container);
        }

        /**
         * This variable will contains all registered control
         * @var object
         */
        JSNVisualDesign.controls = {};
        JSNVisualDesign.controlGroups = {};
        JSNVisualDesign.toolboxTarget = null;
        JSNVisualDesign.optionsBox = null;
        JSNVisualDesign.optionsBoxContent = null;
        JSNVisualDesign.toolbox = null;
        JSNVisualDesign.wrapper = null;

        JSNVisualDesign.initialize = function (language) {
            JSNVisualDesign.wrapper = $('<div class="form-element ui-state-default jsn-iconbar-trigger"><div class="form-element-content"></div><div class="form-element-overlay"></div><div class="jsn-iconbar"><a href="#" onclick="return false;" title="Edit page" class="element-edit"><i class="icon-pencil"></i></a><a href="#" title="Delete page" onclick="return false;" class="element-delete"><i class="icon-trash"></i></a></div></div>');
            JSNVisualDesign.toolbox = $('<div class="box jsn-bootstrap"></div>');
            JSNVisualDesign.toolboxContent = $('<div class="popover top" />');
            JSNVisualDesign.toolboxContent.css('display', 'block');
            JSNVisualDesign.toolboxContent.append($('<div class="arrow" />'));
            JSNVisualDesign.toolboxContent.append($('<h3 class="popover-title">Select Field</h3>'));
            JSNVisualDesign.toolboxContent.append($('<div class="popover-content"><form><div id="visualdesign-toolbox-static"><fieldset><legend>Static</legend><div class="controls"></div></fieldset></div><div id="visualdesign-toolbox-standard"><fieldset><legend>Standard</legend><div class="controls"></div></fieldset></div><div id="visualdesign-toolbox-extra"><fieldset><legend>Advanced</legend><div class="controls"></div></fieldset></div></form></div>'));
            JSNVisualDesign.toolbox.append(JSNVisualDesign.toolboxContent);
            JSNVisualDesign.toolbox.attr('id', 'visualdesign-toolbox');
            JSNVisualDesign.optionsBox = $('<div class="box jsn-bootstrap" id="visualdesign-options"></div>');
            JSNVisualDesign.optionsBoxContent = $('<div class="popover bottom"></div>');
            JSNVisualDesign.optionsBoxContent.css('display', 'block');
            JSNVisualDesign.optionsBoxContent.append($('<div class="arrow" />'));
            JSNVisualDesign.optionsBoxContent.append($('<h3 class="popover-title">Properties</h3>'));
            JSNVisualDesign.optionsBoxContent.append($('<div class="popover-content"><form><div class="tabs"><ul><li class="active"><a data-toggle="tab" href="#visualdesign-options-general">General</a></li><li><a data-toggle="tab" href="#visualdesign-options-values">Values</a></li></ul><div id="visualdesign-options-general" class="tab-pane active"></div><div id="visualdesign-options-values" class="tab-pane"></div></div></form></div>'));
            JSNVisualDesign.optionsBox.append(JSNVisualDesign.optionsBoxContent);
            JSNVisualDesign.optionsBoxContent.find('form').change(function () {
                var activeElement = JSNVisualDesign.optionsBox.data('visualdesign-active-element');
                if (activeElement) {
                    var options = activeElement.data('visualdesign-element-data');
                    if (options) {
                        var optionsNew = $(this).toJSON();
                        optionsNew.identify = options.options.identify;
                        var newElement = JSNVisualDesign.createElement(options.type, optionsNew, options.id);
                        activeElement.replaceWith(newElement);
                        JSNVisualDesign.optionsBox.data('visualdesign-active-element', newElement);
                        if (options.type == "email") {
                            checkChangeEmail = true;
                        }
                        if (options.type == "date") {
                            JSNVisualDesign.dateTime();
                        }
                        newElement.addClass("ui-state-edit");
                    }
                }
                $('input, textarea').placeholder();
                $(".control-group.jsn-hidden-field").parents(".form-element").addClass("jsn-disabled");
            }).submit(function (e) {
                    $(this).trigger('change');
                    e.preventDefault();
                });
            $(function () {
                $(document).mousedown(function (event) {
                    if (event.target != JSNVisualDesign.toolbox.get(0) && !$.contains(JSNVisualDesign.toolbox.get(0), event.target)) {
                        JSNVisualDesign.closeToolbox();
                    }
                    if (event.target != JSNVisualDesign.optionsBox.get(0) && !$.contains(JSNVisualDesign.optionsBox.get(0), event.target) && $(event.target).parent().attr("class") != "form-element ui-state-edit" && $(event.target).parent().attr("class") != "ui-state-edit" && !$(event.target).parents("#ui-datepicker-div").size() && $(event.target).attr("id") != "ui-datepicker-div" && !$(event.target).parents(".control-list-action").size() && $(event.target).attr("class") != "jsn-lock-screen") {
                        JSNVisualDesign.closeOptionsBox();
                        $("#form-design .ui-state-edit").removeClass("ui-state-edit");
                    }

                });
            });
            JSNVisualControls(JSNVisualDesign, language);
        };
        JSNVisualDesign.setLayout = function (selector, name) {
            var container = $(selector);
            var instance = container.data('visualdesign-instance');
            var elements = instance.serialize(true);

            $.get('index.php?option=com_uniform&task=layout.load&name=' + name + '&format=raw', function (response) {
                container.html(response);
                instance.init(container);
                instance.setElements(elements);
            });
        };

        /**
         * Register control item that can use for page design
         * @param string identify
         * @param object options
         */
        JSNVisualDesign.register = function (identify, options) {
            if (JSNVisualDesign.controls[identify] !== undefined || identify === undefined || identify == '' || options.caption === undefined || options.caption == '' || options.defaults === undefined || !$.isPlainObject(options.defaults) || options.params === undefined || !$.isPlainObject(options.params) || options.tmpl === undefined || options.tmpl == '') {
                return false;
            }
            if (JSNVisualDesign.controlGroups[options.group] === undefined) {
                JSNVisualDesign.controlGroups[options.group] = [];
            }
            // Save control to list
            //options.identify;
            JSNVisualDesign.controls[identify] = options;
            JSNVisualDesign.controlGroups[options.group].push(identify);
        };

        /**
         * Draw registered buttons to toolbox
         * @return void
         */
        JSNVisualDesign.drawToolboxButtons = function () {
            $.map(JSNVisualDesign.controlGroups, function (buttons, group) {
                var tab = JSNVisualDesign.toolbox.find('div#visualdesign-toolbox-' + group + ' .controls');
                var container = $('<div/>', {
                    'class':'jsn-columns-container jsn-columns-count-three'
                });
                $(buttons).each(function (index, identify) {
                    if (identify != "form-actions") {
                        var options = JSNVisualDesign.controls[identify];
                        var button = $('<div/>', {
                            'class':'jsn-column-item'
                        }).append($('<div/>', {
                            'class':'jsn-padding-small'
                        }).append($('<button/>', {
                            'name':identify,
                            'class':'btn'
                        }).click(function (e) {
                                if (JSNVisualDesign.getField()) {
                                    if (JSNVisualDesign.toolboxTarget == null)
                                        JSNVisualDesign.closeToolbox();
                                    var control = JSNVisualDesign.controls[this.name];

                                    control.defaults.identify = "jsn_tmp_" + Math.floor(Math.random() * 1000000);
                                    var element = JSNVisualDesign.createElement(this.name, control.defaults);
                                    element.appendTo(JSNVisualDesign.toolboxTarget);
                                    element.find("a.element-edit").click();
                                    if (this.name == "dropdown") {
                                        $("#option-firstItemAsPlaceholder-checkbox").prop("checked", true);
                                    }
                                    if (this.name == "date") {
                                        $("#option-dateFormat-checkbox").prop("checked", true);
                                        JSNVisualDesign.eventChangeDate();
                                    }
                                    if (this.name == "address") {
                                        $("#jsn-field-address .jsn-item input[type=checkbox]").each(function () {
                                            $(this).prop("checked", true);
                                        });
                                        JSNVisualDesign.eventChangeAddress();
                                    }
                                    if (this.name == "name") {
                                        $("#jsn-field-name .jsn-items-list input[type=checkbox]").each(function () {
                                            $(this).prop("checked", true);
                                        });
                                        JSNVisualDesign.eventChangeName();
                                    }
                                    JSNVisualDesign.savePage();
                                    JSNVisualDesign.closeToolbox();
                                    JSNVisualDesign.optionsBox.find('form').trigger("change");
                                    $('html, body').animate({
                                        scrollTop:$("#form-container").height()
                                    }, 800);
                                }
                                e.preventDefault();
                            }).append($('<i/>', {
                            'class':'jsn-icon16 icon-formfields jsn-icon-' + identify
                        })).append(options.caption)))
                        container.append(button);
                    }
                });

                container.append($('<div/>', {
                    'class':'clearbreak'
                }));

                if (!$.contains(tab.get(0), container.get(0))) {
                    tab.append(container);
                }
            });
        };

        /**
         * Create element for add to design page
         * @param type
         * @param data
         */
        JSNVisualDesign.createElement = function (type, opts, id) {
            var control = JSNVisualDesign.controls[type];
            if (control) {
                var data = (opts === undefined) ? control.defaults : $.extend({}, control.defaults, opts);
                var wrapper = JSNVisualDesign.wrapper.clone();
                wrapper.data('visualdesign-element-data', {
                    id:id,
                    type:type,
                    options:data
                });

                wrapper.find('.form-element-content').append($.tmpl(control.tmpl, data));
                wrapper.find('.element-delete').click(function () {
                    $("#form-design-header .jsn-iconbar").css("display", "none");
                    $(".jsn-page-actions").css("display", "none");
                    var eventClick = this;
                    if (id) {
                        $(".jsn-modal-overlay,.jsn-modal-indicator").remove();
                        $("body").append($("<div/>", {
                            "class":"jsn-modal-overlay",
                            "style":"z-index: 1000; display: inline;"
                        })).append($("<div/>", {
                            "class":"jsn-modal-indicator",
                            "style":"display:block"
                        }));
                        $.ajax({
                            type:"POST",
                            dataType:'json',
                            url:"index.php?option=com_uniform&view=form&task=form.getcountfield&tmpl=component",
                            data:{
                                field_id:id,
                                form_id:$("#jform_form_id").val()
                            },
                            success:function (response) {
                                $(".jsn-modal-overlay,.jsn-modal-indicator").remove();
                                if (response > 0) {
                                    $("#confirmRemoveField").remove();
                                    $(eventClick).after(
                                        $("<div/>", {
                                            "id":"confirmRemoveField"
                                        }).append(
                                            $("<div/>", {
                                                "class":"ui-dialog-content-inner jsn-bootstrap"
                                            }).append($("<p/>").append(lang['JSN_UNIFORM_CONFIRM_DELETING_A_FIELD_DES']))
                                                .append(
                                                $("<div/>", {
                                                    "class":"form-actions"
                                                }).append(
                                                    $("<button/>", {
                                                        "class":"btn",
                                                        text:lang["JSN_UNIFORM_BTN_BACKUP"]
                                                    }).click(function () {
                                                            window.open("index.php?option=com_uniform&view=configuration&s=maintenance&g=data#data-back-restore", 'backupdata');
                                                        })))));
                                    $("#confirmRemoveField").dialog({
                                        height:300,
                                        width:500,
                                        title:lang["JSN_UNIFORM_CONFIRM_DELETING_A_FIELD"],
                                        draggable:false,
                                        resizable:false,
                                        autoOpen:true,
                                        modal:true,
                                        buttons:{
                                            Yes:function () {
                                                wrapper.remove();
                                                JSNVisualDesign.savePage();
                                                $("#confirmRemoveField").dialog('close');
                                                $("#confirmRemoveField").remove();
                                            },
                                            No:function () {
                                                $("#confirmRemoveField").dialog('close');
                                                $("#confirmRemoveField").remove();
                                            }
                                        }
                                    });
                                } else {
                                    wrapper.remove();
                                    JSNVisualDesign.savePage('delete');
                                }
                            }
                        });
                    } else {
                        wrapper.remove();
                        JSNVisualDesign.savePage('delete');
                    }


                });
                wrapper.find("a.element-edit").click(function (event) {
                    $("#form-design .ui-state-edit").removeClass("ui-state-edit");
                    wrapper.addClass("ui-state-edit");
                    JSNVisualDesign.openOptionsBox(wrapper, type, wrapper.data('visualdesign-element-data').options, $(this));
                });

                /*wrapper.click(function(event){
                 JSNVisualDesign.optionsBox.find('form').change();
                 event.stopPropagation();
                 event.preventDefault();
                 })*/
                return wrapper;
            }

        };

        /**
         * Open toolbox to insert new element
         * @param target The target to insert element
         */
        JSNVisualDesign.openToolbox = function (sender, target) {
            if (!JSNVisualDesign.getField()) {
                JSNUniformDialogEdition.createDialogLimitation($("#form-container"), lang["JSN_UNIFORM_YOU_HAVE_REACHED_THE_LIMITATION_OF_10_FIELD_IN_FREE_EDITION"]);
                return false;
            }

            if (JSNVisualDesign.toolbox.find('button.btn').size() == 0)
                JSNVisualDesign.drawToolboxButtons();
            JSNVisualDesign.closeOptionsBox();
            JSNVisualDesign.toolbox.hide().appendTo($('body')).show();

            JSNVisualDesign.position(JSNVisualDesign.toolbox, sender, 'top', {
                top:-5
            });

            JSNVisualDesign.toolboxTarget = target;
        };

        JSNVisualDesign.closeToolbox = function () {
            JSNVisualDesign.toolbox.hide();
        };
        JSNVisualDesign.savePage = function (action) {
            var container = $("#form-container");
            var listOptionPage = [];
            var instance = container.data('visualdesign-instance');
            var content = "";
            var serialize = instance.serialize(true);
            if (serialize != "" && serialize != "[]") {
                content = $.toJSON(serialize);
            }
            $(" ul.jsn-page-list li.page-items").each(function () {
                listOptionPage.push([$(this).find("input").attr('data-id'), $(this).find("input").attr('value')]);
            });

            $.ajax({
                type:"POST",
                dataType:'json',
                url:"index.php?option=com_uniform&view=form&task=form.savepage&tmpl=component",
                data:{
                    form_id:$("#jform_form_id").val(),
                    form_content:content,
                    form_page_name:$("#form-design-header").attr('data-value'),
                    form_list_page:listOptionPage
                },
                success:function () {
                    JSNVisualDesign.emailNotification();
                    JSNVisualDesign.getField();
                    if (action == 'delete') {
                        $("#form-design-header .jsn-iconbar").css("display", "");
                        $(".jsn-page-actions").css("display", "");
                    }
                }
            });
        }
        JSNVisualDesign.getField = function () {
            if (edition.toLowerCase() == "free") {
                var container = $("#form-container");
                if ($("#form-container").size()) {
                    var instance = container.data('visualdesign-instance');
                    var formContent = instance.serialize(true);
                    if (formContent.length > 9) {
                        return false;
                    } else {
                        return true;
                    }
                }

            } else {
                return true;
            }
        }
        JSNVisualDesign.emailNotification = function () {
            var container = $("#form-container");
            var instance = container.data('visualdesign-instance');
            var formContent = instance.serialize(true);
            var content = "";

            if (formContent != "" && formContent != "[]") {
                content = $.toJSON(formContent);
            }
            var check = 0;
            var listOptionPage = [];

            $(" ul.jsn-page-list li.page-items").each(function () {
                listOptionPage.push([$(this).find("input").attr('data-id'), $(this).find("input").attr('value')]);
            });
            $.ajax({
                type:"POST",
                dataType:'json',
                url:"index.php?option=com_uniform&view=form&task=form.loadsessionfield&tmpl=component",
                data:{
                    form_id:$("#jform_form_id").val(),
                    form_page_name:$("#form-design-header").attr('data-value'),
                    form_content:content,
                    form_list_page:listOptionPage
                },
                success:function (response) {
                    $("#email .email-submitters .jsn-items-list").html("");
                    if (response) {
                        $.each(response, function (i, item) {
                            if (item.type == 'email') {
                                check++;
                                if ($.inArray(item.identify, dataEmailSubmitter) != -1) {

                                    $("#email .email-submitters .jsn-items-list").append(
                                        $("<div/>", {
                                            "class":"jsn-item ui-state-default"
                                        }).append(
                                            $("<label/>", {
                                                "class":"checkbox",
                                                text:item.options.label
                                            }).append(
                                                $("<input/>", {
                                                    "type":"checkbox",
                                                    "name":"form_submitter[]",
                                                    "checked":"checked",
                                                    "class":"jsn-check-submitter",
                                                    "value":item.identify
                                                }))))
                                } else {
                                    $("#email .email-submitters .jsn-items-list").append(
                                        $("<div/>", {
                                            "class":"jsn-item ui-state-default"
                                        }).append(
                                            $("<label/>", {
                                                "class":"checkbox",
                                                text:item.options.label
                                            }).append(
                                                $("<input/>", {
                                                    "type":"checkbox",
                                                    "name":"form_submitter[]",
                                                    "class":"jsn-check-submitter",
                                                    "value":item.identify
                                                }))))
                                }
                            }
                        });
                    }
                    if (check == 0 || !check) {
                        $("#email .email-submitters .jsn-items-list").append(
                            $("<div/>", {
                                "class":"ui-state-default ui-state-disabled",
                                "text":lang["JSN_UNIFORM_NO_EMAIL"],
                                "title":lang["JSN_UNIFORM_NO_EMAIL_DES"]
                            }))
                    }
                    $("#email .email-submitters .jsn-items-list").parent().parent().show();
                }
            });
        };
        JSNVisualDesign.checklimitFileSize = function () {
            if ($("#visualdesign-options #visualdesign-options-general #option-limitFileSize-checkbox").is(':checked')) {
                $("#visualdesign-options #visualdesign-options-general #option-maxSize-number").removeAttr("disabled");
                $("#visualdesign-options #visualdesign-options-general #option-maxSizeUnit-select").removeAttr("disabled");
                $("#visualdesign-options #visualdesign-options-general #limit-size-upload").hide();
            } else {
                $("#visualdesign-options #visualdesign-options-general #option-maxSize-number").attr("disabled", "disabled");
                $("#visualdesign-options #visualdesign-options-general #option-maxSizeUnit-select").attr("disabled", "disabled");
                $("#visualdesign-options #visualdesign-options-general #limit-size-upload").show();
            }
        };
        JSNVisualDesign.checklimitFileExtensions = function () {
            if ($("#visualdesign-options #visualdesign-options-general #option-limitFileExtensions-checkbox").is(':checked')) {
                $("#visualdesign-options #visualdesign-options-general #option-allowedExtensions-text").removeAttr("disabled");
                $("#visualdesign-options #visualdesign-options-general #limit-extensions").attr("original-title", lang["JSN_UNIFORM_FOR_SECURITY_REASONS_FOLLOWING_FILE_EXTENSIONS"] + "php, phps, php3, php4, phtml, pl, py, jsp, asp, htm, shtml, sh, cgi, htaccess, exe, dll. ");
            } else {
                $("#visualdesign-options #visualdesign-options-general #option-allowedExtensions-text").attr("disabled", "disabled");
                $("#visualdesign-options #visualdesign-options-general #limit-extensions").attr("original-title", lang["JSN_UNIFORM_FORM_LIMIT_FILE_EXTENSIONS"] + limitEx + ". \n" + lang["JSN_UNIFORM_FOR_SECURITY_REASONS_FOLLOWING_FILE_EXTENSIONS"] + "php, phps, php3, php4, phtml, pl, py, jsp, asp, htm, shtml, sh, cgi, htaccess, exe, dll. ");
            }
        };
        JSNVisualDesign.checkLimitation = function () {
            if ($("#visualdesign-options-values #option-limitation-checkbox").is(':checked')) {
                $("#visualdesign-options-values #option-limitMin-number").removeAttr("disabled");
                $("#visualdesign-options-values #option-limitMax-number").removeAttr("disabled");
                $("#visualdesign-options-values #option-limitType-select").removeAttr("disabled");
            } else {
                $("#visualdesign-options-values #option-limitMin-number").attr("disabled", "disabled");
                $("#visualdesign-options-values #option-limitMax-number").attr("disabled", "disabled");
                $("#visualdesign-options-values #option-limitType-select").attr("disabled", "disabled");
            }
        };
        JSNVisualDesign.eventChangeDate = function () {
            var dateFormat = "mm/dd/yy";
            var formatDate = "";
            if ($("#option-dateOptionFormat-select").val() == "custom") {
                formatDate = $("#jsn-custom-date-field").val();
                $("#jsn-custom-date-field").change(function () {
                    JSNVisualDesign.eventChangeDate();
                });
                $("#jsn-custom-date").removeClass("hide");
            } else {
                formatDate = $("#option-dateOptionFormat-select").val();
                $("#jsn-custom-date").addClass("hide");
            }
            if ($("#option-dateFormat-checkbox").is(':checked')) {
                dateFormat = formatDate;
            }

            var divAppend = $("input.input-date-time").parent();
            var dateValue = $("#option-dateValue-text").datetimepicker('getDate');
            var dateRageValue = $("#option-dateValueRange-text").datetimepicker('getDate');
            $("input.input-date-time").datepicker("destroy");
            $(divAppend).attr("class", "input-append jsn-inline");
            var timeFormat = $("#option-timeOptionFormat-select").val();
            $("#option-timeFormat-checkbox").show();
            $("#option-dateFormat-checkbox").show();
            $(".jsn-tmp-date").remove();
            var yearRangeList = [];
            var yearRangeMax = $("#option-yearRangeMax-text").val();
            var yearRangeMin = $("#option-yearRangeMin-text").val();
            if (yearRangeMin && yearRangeMax) {
                yearRangeList.push(yearRangeMin);
                yearRangeList.push(yearRangeMax);
            } else if (yearRangeMin) {
                yearRangeList.push(yearRangeMin);
                yearRangeList.push((new Date).getFullYear());
            } else if (yearRangeMax) {
                yearRangeList.push(yearRangeMax);
                yearRangeList.push((new Date).getFullYear());
            }
            var yearRange = "1930:+0";
            if (yearRangeList.length) {
                yearRange = yearRangeList.join(":");
            }
            if ($("#option-timeFormat-checkbox").is(':checked') && $("#option-dateFormat-checkbox").is(':checked')) {
                $("input.input-date-time").datetimepicker({
                    changeMonth:true,
                    changeYear:true,
                    showOn:"button",
                    yearRange:yearRange,
                    dateFormat:dateFormat,
                    timeFormat:timeFormat,
                    hourText:lang['JSN_UNIFORM_DATE_HOUR_TEXT'],
                    minuteText:lang['JSN_UNIFORM_DATE_MINUTE_TEXT'],
                    closeText:lang['JSN_UNIFORM_DATE_CLOSE_TEXT'],
                    prevText:lang['JSN_UNIFORM_DATE_PREV_TEXT'],
                    nextText:lang['JSN_UNIFORM_DATE_NEXT_TEXT'],
                    currentText:lang['JSN_UNIFORM_DATE_CURRENT_TEXT'],
                    monthNames:[lang['JSN_UNIFORM_DATE_MONTH_JANUARY'],
                        lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY'],
                        lang['JSN_UNIFORM_DATE_MONTH_MARCH'],
                        lang['JSN_UNIFORM_DATE_MONTH_APRIL'],
                        lang['JSN_UNIFORM_DATE_MONTH_MAY'],
                        lang['JSN_UNIFORM_DATE_MONTH_JUNE'],
                        lang['JSN_UNIFORM_DATE_MONTH_JULY'],
                        lang['JSN_UNIFORM_DATE_MONTH_AUGUST'],
                        lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER'],
                        lang['JSN_UNIFORM_DATE_MONTH_OCTOBER'],
                        lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER'],
                        lang['JSN_UNIFORM_DATE_MONTH_DECEMBER']],
                    monthNamesShort:[lang['JSN_UNIFORM_DATE_MONTH_JANUARY_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_MARCH_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_APRIL_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_MAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_JUNE_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_JULY_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_AUGUST_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_OCTOBER_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_DECEMBER_SHORT']],
                    dayNames:[lang['JSN_UNIFORM_DATE_DAY_SUNDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_MONDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_TUESDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_THURSDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_FRIDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_SATURDAY']],
                    dayNamesShort:[lang['JSN_UNIFORM_DATE_DAY_SUNDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_MONDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_TUESDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_THURSDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_FRIDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_SATURDAY_SHORT']],
                    dayNamesMin:[lang['JSN_UNIFORM_DATE_DAY_SUNDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_MONDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_TUESDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_THURSDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_FRIDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_SATURDAY_MIN']],
                    weekHeader:lang['JSN_UNIFORM_DATE_DAY_WEEK_HEADER']
                }).removeClass("jsn-input-xxlarge-fluid input-small input-medium").addClass("input-medium");
                if (dateValue) {
                    $("#option-dateValue-text").datetimepicker('setDate', dateValue);
                }
                if (dateRageValue) {
                    $("#option-dateValueRange-text").datetimepicker('setDate', dateRageValue);
                }
            } else if ($("#option-timeFormat-checkbox").is(':checked')) {
                $("input.input-date-time").timepicker({
                    changeMonth:true,
                    changeYear:true,
                    showOn:"button",
                    timeFormat:timeFormat,
                    hourText:lang['JSN_UNIFORM_DATE_HOUR_TEXT'],
                    minuteText:lang['JSN_UNIFORM_DATE_MINUTE_TEXT'],
                    closeText:lang['JSN_UNIFORM_DATE_CLOSE_TEXT'],
                    prevText:lang['JSN_UNIFORM_DATE_PREV_TEXT'],
                    nextText:lang['JSN_UNIFORM_DATE_NEXT_TEXT'],
                    currentText:lang['JSN_UNIFORM_DATE_CURRENT_TEXT'],
                    monthNames:[lang['JSN_UNIFORM_DATE_MONTH_JANUARY'],
                        lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY'],
                        lang['JSN_UNIFORM_DATE_MONTH_MARCH'],
                        lang['JSN_UNIFORM_DATE_MONTH_APRIL'],
                        lang['JSN_UNIFORM_DATE_MONTH_MAY'],
                        lang['JSN_UNIFORM_DATE_MONTH_JUNE'],
                        lang['JSN_UNIFORM_DATE_MONTH_JULY'],
                        lang['JSN_UNIFORM_DATE_MONTH_AUGUST'],
                        lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER'],
                        lang['JSN_UNIFORM_DATE_MONTH_OCTOBER'],
                        lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER'],
                        lang['JSN_UNIFORM_DATE_MONTH_DECEMBER']],
                    monthNamesShort:[lang['JSN_UNIFORM_DATE_MONTH_JANUARY_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_MARCH_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_APRIL_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_MAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_JUNE_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_JULY_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_AUGUST_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_OCTOBER_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_DECEMBER_SHORT']],
                    dayNames:[lang['JSN_UNIFORM_DATE_DAY_SUNDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_MONDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_TUESDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_THURSDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_FRIDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_SATURDAY']],
                    dayNamesShort:[lang['JSN_UNIFORM_DATE_DAY_SUNDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_MONDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_TUESDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_THURSDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_FRIDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_SATURDAY_SHORT']],
                    dayNamesMin:[lang['JSN_UNIFORM_DATE_DAY_SUNDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_MONDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_TUESDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_THURSDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_FRIDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_SATURDAY_MIN']],
                    weekHeader:lang['JSN_UNIFORM_DATE_DAY_WEEK_HEADER']
                }).removeClass("jsn-input-xxlarge-fluid input-small input-medium").addClass("input-small");
                if (dateValue) {
                    $("#option-dateValue-text").timepicker('setTime', dateValue);
                }
                if (dateRageValue) {
                    $("#option-dateValueRange-text").timepicker('setTime', dateRageValue);
                }
                $("#option-timeFormat-checkbox").before($("<input/>", {
                    "class":"jsn-tmp-date",
                    "type":"checkbox",
                    "disabled":true,
                    "checked":true
                })).hide();
            } else {
                $("#option-dateFormat-checkbox").prop("checked", true);
                $("input.input-date-time").datepicker({
                    changeMonth:true,
                    changeYear:true,
                    showOn:"button",
                    yearRange:yearRange,
                    dateFormat:dateFormat,
                    hourText:lang['JSN_UNIFORM_DATE_HOUR_TEXT'],
                    minuteText:lang['JSN_UNIFORM_DATE_MINUTE_TEXT'],
                    closeText:lang['JSN_UNIFORM_DATE_CLOSE_TEXT'],
                    prevText:lang['JSN_UNIFORM_DATE_PREV_TEXT'],
                    nextText:lang['JSN_UNIFORM_DATE_NEXT_TEXT'],
                    currentText:lang['JSN_UNIFORM_DATE_CURRENT_TEXT'],
                    monthNames:[lang['JSN_UNIFORM_DATE_MONTH_JANUARY'],
                        lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY'],
                        lang['JSN_UNIFORM_DATE_MONTH_MARCH'],
                        lang['JSN_UNIFORM_DATE_MONTH_APRIL'],
                        lang['JSN_UNIFORM_DATE_MONTH_MAY'],
                        lang['JSN_UNIFORM_DATE_MONTH_JUNE'],
                        lang['JSN_UNIFORM_DATE_MONTH_JULY'],
                        lang['JSN_UNIFORM_DATE_MONTH_AUGUST'],
                        lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER'],
                        lang['JSN_UNIFORM_DATE_MONTH_OCTOBER'],
                        lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER'],
                        lang['JSN_UNIFORM_DATE_MONTH_DECEMBER']],
                    monthNamesShort:[lang['JSN_UNIFORM_DATE_MONTH_JANUARY_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_MARCH_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_APRIL_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_MAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_JUNE_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_JULY_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_AUGUST_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_OCTOBER_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER_SHORT'],
                        lang['JSN_UNIFORM_DATE_MONTH_DECEMBER_SHORT']],
                    dayNames:[lang['JSN_UNIFORM_DATE_DAY_SUNDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_MONDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_TUESDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_THURSDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_FRIDAY'],
                        lang['JSN_UNIFORM_DATE_DAY_SATURDAY']],
                    dayNamesShort:[lang['JSN_UNIFORM_DATE_DAY_SUNDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_MONDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_TUESDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_THURSDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_FRIDAY_SHORT'],
                        lang['JSN_UNIFORM_DATE_DAY_SATURDAY_SHORT']],
                    dayNamesMin:[lang['JSN_UNIFORM_DATE_DAY_SUNDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_MONDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_TUESDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_THURSDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_FRIDAY_MIN'],
                        lang['JSN_UNIFORM_DATE_DAY_SATURDAY_MIN']],
                    weekHeader:lang['JSN_UNIFORM_DATE_DAY_WEEK_HEADER']
                }).removeClass("jsn-input-xxlarge-fluid input-small input-medium").addClass("input-small");
                if (dateValue) {
                    $("#option-dateValue-text").datepicker('setDate', dateValue);
                }
                if (dateRageValue) {
                    $("#option-dateValueRange-text").datepicker('setDate', dateRageValue);
                }
                $("#option-dateFormat-checkbox").before($("<input/>", {
                    "class":"jsn-tmp-date",
                    "type":"checkbox",
                    "disabled":true,
                    "checked":true
                })).hide();
            }
            $("button.ui-datepicker-trigger").addClass("btn btn-icon").html($("<i/>", {
                "class":"icon-calendar"
            }));
            if ($("#option-enableRageSelection-checkbox").is(':checked')) {
                $("input#option-dateValueRange-text").parent().show();
            } else {
                $("input#option-dateValueRange-text").parent().hide();
            }
        };
        JSNVisualDesign.dateTime = function () {
            $('input.uniform-date-time').each(function () {
                if ($(this).attr("dateFormat") || $(this).attr("timeFormat")) {
                    $(this).datetimepicker({
                        changeMonth:true,
                        changeYear:true,
                        showOn:"button",
                        hourText:lang['JSN_UNIFORM_DATE_HOUR_TEXT'],
                        minuteText:lang['JSN_UNIFORM_DATE_MINUTE_TEXT'],
                        closeText:lang['JSN_UNIFORM_DATE_CLOSE_TEXT'],
                        prevText:lang['JSN_UNIFORM_DATE_PREV_TEXT'],
                        nextText:lang['JSN_UNIFORM_DATE_NEXT_TEXT'],
                        currentText:lang['JSN_UNIFORM_DATE_CURRENT_TEXT'],
                        monthNames:[lang['JSN_UNIFORM_DATE_MONTH_JANUARY'],
                            lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY'],
                            lang['JSN_UNIFORM_DATE_MONTH_MARCH'],
                            lang['JSN_UNIFORM_DATE_MONTH_APRIL'],
                            lang['JSN_UNIFORM_DATE_MONTH_MAY'],
                            lang['JSN_UNIFORM_DATE_MONTH_JUNE'],
                            lang['JSN_UNIFORM_DATE_MONTH_JULY'],
                            lang['JSN_UNIFORM_DATE_MONTH_AUGUST'],
                            lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER'],
                            lang['JSN_UNIFORM_DATE_MONTH_OCTOBER'],
                            lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER'],
                            lang['JSN_UNIFORM_DATE_MONTH_DECEMBER']],
                        monthNamesShort:[lang['JSN_UNIFORM_DATE_MONTH_JANUARY_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_MARCH_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_APRIL_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_MAY_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_JUNE_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_JULY_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_AUGUST_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_OCTOBER_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_DECEMBER_SHORT']],
                        dayNames:[lang['JSN_UNIFORM_DATE_DAY_SUNDAY'],
                            lang['JSN_UNIFORM_DATE_DAY_MONDAY'],
                            lang['JSN_UNIFORM_DATE_DAY_TUESDAY'],
                            lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY'],
                            lang['JSN_UNIFORM_DATE_DAY_THURSDAY'],
                            lang['JSN_UNIFORM_DATE_DAY_FRIDAY'],
                            lang['JSN_UNIFORM_DATE_DAY_SATURDAY']],
                        dayNamesShort:[lang['JSN_UNIFORM_DATE_DAY_SUNDAY_SHORT'],
                            lang['JSN_UNIFORM_DATE_DAY_MONDAY_SHORT'],
                            lang['JSN_UNIFORM_DATE_DAY_TUESDAY_SHORT'],
                            lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_SHORT'],
                            lang['JSN_UNIFORM_DATE_DAY_THURSDAY_SHORT'],
                            lang['JSN_UNIFORM_DATE_DAY_FRIDAY_SHORT'],
                            lang['JSN_UNIFORM_DATE_DAY_SATURDAY_SHORT']],
                        dayNamesMin:[lang['JSN_UNIFORM_DATE_DAY_SUNDAY_MIN'],
                            lang['JSN_UNIFORM_DATE_DAY_MONDAY_MIN'],
                            lang['JSN_UNIFORM_DATE_DAY_TUESDAY_MIN'],
                            lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_MIN'],
                            lang['JSN_UNIFORM_DATE_DAY_THURSDAY_MIN'],
                            lang['JSN_UNIFORM_DATE_DAY_FRIDAY_MIN'],
                            lang['JSN_UNIFORM_DATE_DAY_SATURDAY_MIN']],
                        weekHeader:lang['JSN_UNIFORM_DATE_DAY_WEEK_HEADER']
                    });
                } else {
                    $(this).datepicker({
                        changeMonth:true,
                        changeYear:true,
                        showOn:"button",
                        hourText:lang['JSN_UNIFORM_DATE_HOUR_TEXT'],
                        minuteText:lang['JSN_UNIFORM_DATE_MINUTE_TEXT'],
                        closeText:lang['JSN_UNIFORM_DATE_CLOSE_TEXT'],
                        prevText:lang['JSN_UNIFORM_DATE_PREV_TEXT'],
                        nextText:lang['JSN_UNIFORM_DATE_NEXT_TEXT'],
                        currentText:lang['JSN_UNIFORM_DATE_CURRENT_TEXT'],
                        monthNames:[lang['JSN_UNIFORM_DATE_MONTH_JANUARY'],
                            lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY'],
                            lang['JSN_UNIFORM_DATE_MONTH_MARCH'],
                            lang['JSN_UNIFORM_DATE_MONTH_APRIL'],
                            lang['JSN_UNIFORM_DATE_MONTH_MAY'],
                            lang['JSN_UNIFORM_DATE_MONTH_JUNE'],
                            lang['JSN_UNIFORM_DATE_MONTH_JULY'],
                            lang['JSN_UNIFORM_DATE_MONTH_AUGUST'],
                            lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER'],
                            lang['JSN_UNIFORM_DATE_MONTH_OCTOBER'],
                            lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER'],
                            lang['JSN_UNIFORM_DATE_MONTH_DECEMBER']],
                        monthNamesShort:[lang['JSN_UNIFORM_DATE_MONTH_JANUARY_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_FEBRUARY_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_MARCH_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_APRIL_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_MAY_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_JUNE_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_JULY_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_AUGUST_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_SEPTEMBER_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_OCTOBER_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_NOVEMBER_SHORT'],
                            lang['JSN_UNIFORM_DATE_MONTH_DECEMBER_SHORT']],
                        dayNames:[lang['JSN_UNIFORM_DATE_DAY_SUNDAY'],
                            lang['JSN_UNIFORM_DATE_DAY_MONDAY'],
                            lang['JSN_UNIFORM_DATE_DAY_TUESDAY'],
                            lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY'],
                            lang['JSN_UNIFORM_DATE_DAY_THURSDAY'],
                            lang['JSN_UNIFORM_DATE_DAY_FRIDAY'],
                            lang['JSN_UNIFORM_DATE_DAY_SATURDAY']],
                        dayNamesShort:[lang['JSN_UNIFORM_DATE_DAY_SUNDAY_SHORT'],
                            lang['JSN_UNIFORM_DATE_DAY_MONDAY_SHORT'],
                            lang['JSN_UNIFORM_DATE_DAY_TUESDAY_SHORT'],
                            lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_SHORT'],
                            lang['JSN_UNIFORM_DATE_DAY_THURSDAY_SHORT'],
                            lang['JSN_UNIFORM_DATE_DAY_FRIDAY_SHORT'],
                            lang['JSN_UNIFORM_DATE_DAY_SATURDAY_SHORT']],
                        dayNamesMin:[lang['JSN_UNIFORM_DATE_DAY_SUNDAY_MIN'],
                            lang['JSN_UNIFORM_DATE_DAY_MONDAY_MIN'],
                            lang['JSN_UNIFORM_DATE_DAY_TUESDAY_MIN'],
                            lang['JSN_UNIFORM_DATE_DAY_WEDNESDAY_MIN'],
                            lang['JSN_UNIFORM_DATE_DAY_THURSDAY_MIN'],
                            lang['JSN_UNIFORM_DATE_DAY_FRIDAY_MIN'],
                            lang['JSN_UNIFORM_DATE_DAY_SATURDAY_MIN']],
                        weekHeader:lang['JSN_UNIFORM_DATE_DAY_WEEK_HEADER']
                    });
                }
                $("button.ui-datepicker-trigger").addClass("btn btn-icon").html($("<i/>", {
                    "class":"icon-calendar"
                }));
            });
        };
        JSNVisualDesign.eventChangePhone = function () {
            if ($("#option-format-select").val() == "3-field") {
                $("#visualdesign-options-values #option-value-text").parents(".control-group").addClass("hide");
                $("#visualdesign-options-values #option-oneField-text").parents(".control-group").removeClass("hide");
            } else {
                $("#visualdesign-options-values #option-value-text").parents(".control-group").removeClass("hide");
                $("#visualdesign-options-values #option-oneField-text").parents(".control-group").addClass("hide");
            }
        };
        JSNVisualDesign.eventChangeCurrency = function () {
            if ($("#option-format-select").val() != "Yen") {
                $("#visualdesign-options-values .jsn-field-prefix").show();
                $("#visualdesign-options-values .jsn-inline #option-cents-text").parent().show();
            } else {
                $("#visualdesign-options-values .jsn-field-prefix").hide();
                $("#visualdesign-options-values .jsn-inline #option-cents-text").parent().hide();
            }
        };
        JSNVisualDesign.eventChangeallowOther = function () {
            if ($("#option-allowOther-checkbox").is(':checked')) {
                $("#visualdesign-options-values .jsn-allow-other #option-labelOthers-_text").show();
            } else {
                $("#visualdesign-options-values .jsn-allow-other #option-labelOthers-_text").hide();
            }
        };
        JSNVisualDesign.eventChangeNumber = function () {
            if ($("#option-showDecimal-checkbox").is(':checked')) {
                $("#visualdesign-options-values .jsn-field-prefix").show();
                $("#visualdesign-options-values .jsn-inline #option-decimal-number").parent().show();
            } else {
                $("#visualdesign-options-values .jsn-field-prefix").hide();
                $("#visualdesign-options-values .jsn-inline #option-decimal-number").parent().hide();
            }
        };
        JSNVisualDesign.eventChangeAddress = function () {
            if ($("#option-vcountry-checkbox").is(':checked')) {
                $("#visualdesign-options-values #jsn-address-default-country").show();
            } else {
                $("#visualdesign-options-values #jsn-address-default-country").hide();

            }
        };
        JSNVisualDesign.eventChangeConfirm = function () {
            if ($("#option-requiredConfirm-checkbox").is(':checked')) {
                $("#visualdesign-options-values #option-valueConfirm-text").show();
            } else {
                $("#visualdesign-options-values #option-valueConfirm-text").hide();

            }
        };
        JSNVisualDesign.eventChangeName = function () {
            if ($("#option-vtitle-checkbox").is(':checked')) {
                $("#visualdesign-options-values #jsn-name-default-titles").show();
            } else {
                $("#visualdesign-options-values #jsn-name-default-titles").hide();

            }
        };
        /**
         * Open options editor for an element
         * @param object event
         * @return void
         */
        JSNVisualDesign.openOptionsBox = function (sender, type, params, action) {
            if (JSNVisualDesign.controls[type] === undefined) {
                return;
            }
            JSNVisualDesign.closeToolbox();

            JSNVisualDesign.renderOptionsBox(JSNVisualDesign.controls[type].params, params);

            JSNVisualDesign.optionsBox.data('visualdesign-active-element', sender);
            JSNVisualDesign.optionsBox.appendTo($('body')).show();
            $(".tabs").tabs({
                selected:0
            });
            $("#visualdesign-options-values #option-limitation-checkbox").change(function () {
                JSNVisualDesign.checkLimitation();
            });
            $("#visualdesign-options #visualdesign-options-general #option-limitFileSize-checkbox").change(function () {
                JSNVisualDesign.checklimitFileSize();
            });
            $("#visualdesign-options #visualdesign-options-general #option-limitFileExtensions-checkbox").change(function () {
                JSNVisualDesign.checklimitFileExtensions();
            });
            $("#option-firstItemAsPlaceholder-checkbox").after('<i class="icon-question-sign" original-title="' + lang["JSN_UNIFORM_SET_ITEM_PLACEHOLDER_DES"] + '"></i>')
            if (type == "date") {

                JSNVisualDesign.eventChangeDate();
                $("#option-enableRageSelection-checkbox").change(function () {
                    JSNVisualDesign.eventChangeDate();
                });
                $("#option-dateFormat-checkbox").change(function () {
                    if (!$("#option-timeFormat-checkbox").is(':checked') && !$("#option-dateFormat-checkbox").is(':checked')) {
                        $(this).prop("checked", true);
                    } else {
                        JSNVisualDesign.eventChangeDate();
                    }
                });
                $("#option-timeFormat-checkbox").change(function () {
                    if (!$("#option-timeFormat-checkbox").is(':checked') && !$("#option-dateFormat-checkbox").is(':checked')) {
                        $(this).prop("checked", true);
                    } else {
                        JSNVisualDesign.eventChangeDate();
                    }
                });
                $("#option-yearRangeMax-text,#option-yearRangeMin-text,#option-timeOptionFormat-select").change(function () {
                    JSNVisualDesign.eventChangeDate();
                });
                var valueDateFormat = $("#option-dateOptionFormat-select").val();
                $("#option-dateOptionFormat-select").change(function () {
                    if ($(this).val() != "custom") {
                        valueDateFormat = $("#option-dateOptionFormat-select").val();
                        JSNVisualDesign.eventChangeDate();
                    } else {
                        $("#jsn-custom-date-field").val(valueDateFormat);
                        JSNVisualDesign.eventChangeDate();
                    }
                });
                $("#option-yearRangeMin-text,#option-yearRangeMax-text").keypress(function (e) {
                    if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                        return false;
                    }
                });
            }
            JSNVisualDesign.eventChangeConfirm();
            $("#option-requiredConfirm-checkbox").change(function () {
                JSNVisualDesign.eventChangeConfirm();
            });
            if (type == "phone") {
                JSNVisualDesign.eventChangePhone();
                $("#option-format-select").change(function () {
                    JSNVisualDesign.eventChangePhone();
                });
            }
            if (type == "form-actions") {
                var pageItems = $("ul.jsn-page-list li.page-items");
                if (pageItems.size() <= 1) {
                    $("#option-btnNext-text").parents(".control-group").remove();
                    $("#option-btnPrev-text").parents(".control-group").remove();
                }
            }
            if (type == "currency") {
                JSNVisualDesign.eventChangeCurrency();
                $("#option-format-select").change(function () {
                    JSNVisualDesign.eventChangeCurrency();
                });
            }

            if (type == "number") {
                JSNVisualDesign.eventChangeNumber();
                $("#option-showDecimal-checkbox").change(function () {
                    JSNVisualDesign.eventChangeNumber();
                });
            }
            if (type == "address") {
                JSNVisualDesign.eventChangeAddress();
                $("#option-vcountry-checkbox").change(function () {
                    JSNVisualDesign.eventChangeAddress();
                });
            }
            if (type == "name") {
                JSNVisualDesign.eventChangeName();
                $("#option-vtitle-checkbox").change(function () {
                    JSNVisualDesign.eventChangeName();
                });
            }
            JSNVisualDesign.eventChangeallowOther();
            $("#option-allowOther-checkbox").change(function () {
                JSNVisualDesign.eventChangeallowOther();
            });

            if (type == "file-upload") {
                JSNVisualDesign.checklimitFileSize();
                JSNVisualDesign.checklimitFileExtensions();
                $("#visualdesign-options #visualdesign-options-general #limit-size-upload").attr("original-title", lang["JSN_UNIFORM_FORM_LIMIT_FILE_SIZE"] + limitSize + " MB");
            }
            $("#option-limitMin-number,#option-limitMax-number,#option-rows-number,#option-maxSize-number").keypress(function (e) {
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                    return false;
                }
            });
            if ($("#visualdesign-options-general #option-value-textarea").size()) {

                $("#visualdesign-options-general #option-value-textarea").wysiwyg();
                $($("#option-value-textareaIFrame").document()).keydown(function () {
                    $("#visualdesign-options-general #option-value-textarea").val($($("#option-value-textareaIFrame").document()).find('body').html()).change();
                }).keyup(function () {
                        $("#visualdesign-options-general #option-value-textarea").val($($("#option-value-textareaIFrame").document()).find('body').html()).change();
                    }).mousedown(function () {
                        $("#visualdesign-options-general #option-value-textarea").val($($("#option-value-textareaIFrame").document()).find('body').html()).change();
                    });
                $("#visualdesign-options-general .wysiwyg .panel li a").click(function () {
                    setTimeout(function () {
                        $("#visualdesign-options-general #option-value-textarea").val($($("#option-value-textareaIFrame").document()).find('body').html()).change();
                    }, 10);
                });
                $("#visualdesign-options-general .wysiwyg").width("98%");
            }
            JSNVisualDesign.checkLimitation();
            JSNVisualDesign.position(JSNVisualDesign.optionsBox, sender, 'bottom', {
                bottom:-5
            }, action);
            $('#visualdesign-options .icon-question-sign').tipsy({
                gravity:'se',
                fade:true
            });
            if (JSNVisualDesign.controls[type].params.values) {
                if (JSNVisualDesign.controls[type].params.values.itemAction) {
                    var itemAction = $("#visualdesign-options-values #option-itemAction-hidden").val();
                    if (itemAction) {
                        itemAction = $.evalJSON(itemAction);
                    }
                    if (itemAction) {
                        $("#visualdesign-options-values .jsn-items-list .jsn-item input[name=item-list]").each(function () {
                            var inputItem = $(this);
                            $.each(itemAction, function (i, item) {
                                if (i == $(inputItem).val()) {
                                    $(inputItem).attr("action-show-field", $.toJSON(item.showField));
                                    $(inputItem).attr("action-hide-field", $.toJSON(item.hideField));
                                    if ($(item.showField).length>0 || $(item.hideField).length>0) {
                                        var jsnItem = $(inputItem).parents(".jsn-item");
                                        $(jsnItem).addClass("jsn-highlight");
                                    }else{
                                        var jsnItem = $(inputItem).parents(".jsn-item");
                                        $(jsnItem).removeClass("jsn-highlight");
                                    }
                                }
                            })
                        });
                    }
                }
            }
        };
        /**
         * Close options editor
         * @param object event
         * @return void
         */
        JSNVisualDesign.closeOptionsBox = function () {
            if (checkChangeEmail) {
                JSNVisualDesign.savePage();
            }
            checkChangeEmail = false;
            JSNVisualDesign.optionsBox.hide();

        };
        /**
         * Render UI for options box
         * @param data
         */
        JSNVisualDesign.renderOptionsBox = function (options, data) {
            if (options.general === undefined) {
                JSNVisualDesign.optionsBoxContent.find('a[href^="#visualdesign-options-general"]').parent().hide();
            } else {
                JSNVisualDesign.optionsBoxContent.find('a[href^="#visualdesign-options-general"]').parent().show();
            }
            if (options.values === undefined) {
                JSNVisualDesign.optionsBoxContent.find('a[href^="#visualdesign-options-values"]').parent().hide();
            } else {
                JSNVisualDesign.optionsBoxContent.find('a[href^="#visualdesign-options-values"]').parent().show();
            }
            JSNVisualDesign.optionsBoxContent.find('div[id^="visualdesign-options-"]').removeClass('active').empty();
            JSNVisualDesign.optionsBoxContent.find('div#visualdesign-options-general').addClass('active');
            JSNVisualDesign.optionsBoxContent.find('a[href^="#visualdesign-options-"]').parent().removeClass('active');
            JSNVisualDesign.optionsBoxContent.find('a[href^="#visualdesign-options-general"]').parent().addClass('active');

            $.map(options, function (params, tabName) {
                var tabPane = JSNVisualDesign.optionsBoxContent.find('#visualdesign-options-' + tabName);

                $.map(params, function (elementOptions, name) {
                    // Render for group option

                    if (elementOptions.type == 'group') {
                        var group = null;
                        group = $('<div/>').append($(elementOptions.decorator));
                        group.addClass('group ' + name);
                        $.map(elementOptions.elements, function (itemOptions, itemName) {
                            itemOptions.name = itemName;
                            group.find(itemName.toLowerCase()).replaceWith(JSNVisualDesign.createControl(itemOptions, data[itemName],data.identify));
                        });
                        tabPane.append(group);
                        return false;
                    }
                    if (elementOptions.type == 'horizontal') {
                        var group = null;
                        group = $('<div/>', {
                            "class":"control-group"
                        }).append($("<label/>", {
                            "class":"control-label"
                        }).append(elementOptions.title)).append($("<div/>", {
                            "class":"controls"
                        }).append($(elementOptions.decorator)));
                        $.map(elementOptions.elements, function (itemOptions, itemName) {
                            itemOptions.name = itemName;
                            group.find(itemName.toLowerCase()).replaceWith(JSNVisualDesign.createControl(itemOptions, data[itemName],data.identify));
                        });
                        tabPane.append(group);
                        return false;
                    }
                    elementOptions.name = name;

                    if (elementOptions.name == 'group') {
                        var groupControl = $('<div/>', {
                            'class':'controls'
                        });
                        $.each(elementOptions, function (index, value) {
                            if (index != "name") {
                                value.name = index;
                                value.classLabel = false;
                                groupControl.append(JSNVisualDesign.createControl(value, data[index],data.identify));
                            }
                        });
                        tabPane.append($('<div/>', {
                            'class':'control-group visualdesign-options-group'
                        }).append(groupControl));
                    } else {
                        tabPane.append(JSNVisualDesign.createControl(elementOptions, data[name],data.identify))
                    }
                    JSNVisualDesign.optionsBoxContent.find('a[href^="#visualdesign-options-' + tabName + '"]').parent().show();
                });
            });
            JSNVisualDesign.optionsBoxContent.find('input[type="text"], textarea')
                .bind('keyup', function () {
                    $(this).closest('form').trigger('change');

                });
        };
        /**
         * Create form control to use in editor panel
         * @param type
         * @param name
         * @param data
         * @return
         */
        JSNVisualDesign.createControl = function (options, value,identify) {
            var templates = {
                'hidden':'<input type="hidden" value="${value}" name="${options.name}" id="${id}" />',
                'text':'<div class="controls"><input type="text" value="${value}" name="${options.name}" id="${id}" class="text jsn-input-xxlarge-fluid" /></div>',
                '_text':'<input type="text" value="${value}" name="${options.name}" id="${id}" class="text jsn-input-xxlarge-fluid" />',
                'number':'<div class="controls"><input type="number" value="${value}" name="${options.name}" id="${id}" class="number input-mini" /></div>',
                'select':'<div class="controls"><select name="${options.name}" id="${id}" class="select">{{each(i, val) options.options}}<option value="${i}" {{if val==value || (typeof(i) == "string" && i==value)}}selected{{/if}}>${val}</option>{{/each}}</select></div>',
                'checkbox':'<input type="checkbox" value="1" name="${options.name}" id="${id}" {{if value==1 || value == "1"}}checked{{/if}} />',
                'checkboxes':'<div class="controls">{{each(i, val) options.options}}<label for="${id}-${i}" class="checkbox"><input type="checkbox" name="${options.name}[]" value="${val}" id="${id}-${i}" {{if value.indexOf(val)!=-1}}checked{{/if}} />${val}</label>{{/each}}</div>',
                'radio':'<div class="controls">{{each(i, val) options.options}}<label for="${id}-${i}" class="radio"><input type="radio" name="${options.name}" value="${i}" id="${id}-${i}" {{if value==val}}checked{{/if}} />${val}</label>{{/each}}</div>',
                'textarea':'<div class="controls"><textarea name="${options.name}" id="${id}" rows="3" class="textarea jsn-input-xxlarge-fluid">${value}</textarea></div>'
            };
            var elementId = 'option-' + options.name + '-' + options.type;
            var control = null;
            var element = $('<div/>');

            var setAttributes = function (element, attrs) {
                var elm = $(element),
                    field = elm.is(':input') ? elm : elm.find(':input');
                field.attr(attrs);
            };

            if (templates[options.type] !== undefined) {
                control = $.tmpl(templates[options.type], {
                    options:options,
                    value:value,
                    id:elementId
                });
                if ($.isPlainObject(options.attrs))
                    setAttributes(control, options.attrs);
            } else if (options.type == 'itemlist') {
                control = $.itemList($.extend({}, {
                    listItems:value,
                    id:elementId,
                    identify:identify,
                    language:lang
                }, options));
            } else
                return;
            if (options.label !== undefined && options.classLabel == undefined) {
                element.append($("<div/>", {
                    "class":"control-group"
                }).append($('<label/>', {
                    'for':elementId,
                    text:options.label,
                    'class':'control-label',
                    title:lang[options.title]
                })));
            } else if (!options.classLabel && options.group != "horizontal") {
                element.append($("<div/>", {
                    "class":"control-group "
                }).append($('<label/>', {
                    'for':elementId,
                    text:options.label,
                    title:lang[options.title]
                })));
            }
            if (options.type == 'checkbox') {
                if (options.field == "address" || options.field == "name") {
                    element.find('label').append(control).addClass('checkbox').removeClass("control-label");
                    var contentLabel = element.find('label').remove();
                    element.find(".control-group").attr("class", "jsn-item ui-state-default").append(contentLabel);
                } else if (options.field == "allowOther") {
                    element.find('label').append(control).addClass('checkbox').removeClass("control-label");
                    var contentLabel = element.find('label').remove();
                    element.find(".control-group").parent().append(contentLabel);
                    element.find(".control-group").remove();
                } else {
                    element.find('label').append(control).addClass('checkbox').removeClass("control-label");
                    var contentLabel = element.find('label').remove();
                    element.find(".control-group").append($("<div/>", {
                        "class":"controls"
                    }).append(contentLabel));
                }
            } else {
                if (options.type == "itemlist") {
                    element.find(".control-group").append(control).addClass("jsn-items-list-container");
                } else if (options.group == "horizontal") {
                    if (options.field && (options.field == "horizontal" || options.field == "currency" || options.field == "input-inline")) {
                        $(control).attr("class", "jsn-inline");
                        element.append(control);
                    } else if (options.field && (options.field == "horizontal" || options.field == "number")) {
                        $(control).attr("class", "jsn-inline");
                        element.append(control);
                    } else {
                        $(control).attr("class", "input-append jsn-inline");
                        element.append(control);
                    }
                } else if (options.field == "allowOther") {
                    element.append(control);
                    element.find(".control-group").remove();
                } else {
                    element.find(".control-group").append(control);
                }

            }

            return element.children();
        };

        /**
         * Set position for element that following position of parent element
         * @param element
         * @param parent
         */
        JSNVisualDesign.position = function (e, p, pos, extra, action) {
            var position = {},
                elm = $(e);
            if (action) {
                var parent = $(action);
            } else {
                var parent = $(p);
            }
            //JSNVisualDesign.equalsHeight(elm.find('.tab-pane'));
            var elmStyle = JSNVisualDesign.getBoxStyle(elm),
                parentStyle = JSNVisualDesign.getBoxStyle(parent),
                elmStyleParet = JSNVisualDesign.getBoxStyle($(e).find(".popover"));

            var modalWindow = JSNVisualDesign.getBoxStyle($("#form-design"));
            if (pos === undefined) {
                pos = 'center';
            }

            if (pos == "top" && parentStyle.offset.top < elmStyleParet.outerHeight) {
                pos = "bottom";
                $('html, body').animate({
                    scrollTop:$("#form-container").height()
                }, 800);
            }

            switch (pos) {
                case 'left':
                    position.left = parentStyle.offset.left + (parentStyle.outerWidth - elmStyleParet.outerWidth) / 2;
                    position.top = parentStyle.offset.top;
                    elm.find(".popover").removeClass("top").removeClass("bottom");
                    break;
                case 'center':
                    position.left = parentStyle.offset.left + (parentStyle.outerWidth - elmStyleParet.outerWidth) / 2;
                    position.top = parentStyle.offset.top + parentStyle.outerHeight;
                    elm.find(".popover").removeClass("top").addClass("bottom");
                    break;
                case 'top':
                    position.left = parentStyle.offset.left + (parentStyle.outerWidth - elmStyleParet.outerWidth) / 2;
                    position.top = parentStyle.offset.top - elmStyleParet.outerHeight;
                    elm.find(".popover").removeClass("bottom").addClass("top");
                    break;
                case 'bottom':
                    position.left = parentStyle.offset.left + (parentStyle.outerWidth - elmStyleParet.outerWidth) / 2;
                    position.top = parentStyle.offset.top + parentStyle.outerHeight;
                    elm.find(".popover").removeClass("top").addClass("bottom");
                    break;
            }

            if (extra !== undefined) {
                if (extra.left !== undefined) {
                    position.left = position.left + extra.left;
                }
                if (extra.right !== undefined) {
                    position.right = position.right + extra.right;
                }
                if (extra.top !== undefined) {
                    position.top = position.top + extra.top;
                }
                if (extra.bottom !== undefined) {
                    position.bottom = position.bottom + extra.bottom;
                }
            }

            elm.css(position);
        };

        JSNVisualDesign.getBoxStyle = function (element) {
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
        };

        /**
         * Set all elements to same height
         * @param elements
         */
        JSNVisualDesign.equalsHeight = function (elements) {
            elements.css('height', 'auto');
            var maxHeight = 0;
            elements.each(function () {
                var height = $(this).height();
                if (maxHeight < height)
                    maxHeight = height;
            });

            elements.css('height', maxHeight + 'px');
        };

        /**
         * Generate identify for field based on label
         * @param label
         * @return
         */

        JSNVisualDesign.generateIdentify = function (label, listLabel) {
            var label = label.toLowerCase();
            while (/[^a-zA-Z0-9_]+/.test(label)) {
                label = label.replace(/[^a-zA-Z0-9_]+/, '_');
            }
            var count = 1;
            var identify = label;
            while ($.inArray(identify, listLabel) != -1) {
                identify = label + '_' + count;
                count++;
            }
            return identify;
        };

        JSNVisualDesign.prototype = {
            /**
             * Initialize page for design
             * @param object element
             * @param object options
             */
            init:function (container) {
                JSNVisualDesign.initialize(lang);
                this.JSNUniformDialogEdition = new JSNUniformDialogEdition(this.params);
                this.container = $(container);
                this.document = $(document);
                this.options = {
                    regionSelector:'.form-column',
                    elementSelector:'.form-element',
                    elements:{}
                };

                this.newElement.click(function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    JSNVisualDesign.openToolbox($(e.currentTarget), $(e.currentTarget).prev());
                });
                // Enable sortable
                this.container.data('visualdesign-instance', this).find(this.options.regionSelector + ' .form-region').sortable({
                    items:this.options.elementSelector,
                    connectWith:this.options.regionSelector + ' .form-region',
                    placeholder:'ui-state-highlight',
                    forcePlaceholderSize:true
                }).parent().append(this.newElement);
            },
            clearElements:function () {
                this.container.find('div.form-element').remove();
            },
            /**
             * Add existing elements to designing page
             * @param array elements
             */
            setElements:function (elements) {
                var self = this;

                $(elements).each(function () {
                    this.options.identify = this.identify;
                    var element = JSNVisualDesign.createElement(this.type, this.options, this.id);
                    var column = self.container.find('div[data-column-name="' + this.position + '"] .form-region');

                    if (column.size() == 0) {
                        column = self.container.find('div[data-column-name] .form-default');
                    }
                    column.append(element);

                });
                JSNVisualDesign.getField();
                return self;
            },
            /**
             * Serialize designed page to JSON format for save it to database
             * @return string
             */
            serialize:function (toObject) {
                var serialized = [];
                var serializeObject = toObject || false;
                this.container.find('[data-column-name]').each(function () {
                    var elements = $(this).find('.form-element');
                    var column = $(this).attr('data-column-name');
                    elements.each(function () {
                        var data = $(this).data('visualdesign-element-data');
                        serialized.push({
                            id:data.id,
                            identify:JSNVisualDesign.generateIdentify(data.options.label, listLabel),
                            options:data.options,
                            type:data.type,
                            position:column
                        });

                    });
                });
                $('input, textarea').placeholder();
                $(".control-group.jsn-hidden-field").parents(".form-element").addClass("jsn-disabled");
                JSNVisualDesign.dateTime();
                return serializeObject ? serialized : $.toJSON(serialized);
            }

        };

        /**
         * Plugin for jQuery to serialize a form to JSON format
         * @param options
         * @return
         */
        $.fn.toJSON = function (options) {
            options = $.extend({}, options);

            var self = this,
                json = {},
                push_counters = {},
                patterns = {
                    "validate":/^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
                    "key":/[a-zA-Z0-9_]+|(?=\[\])/g,
                    "push":/^$/,
                    "fixed":/^\d+$/,
                    "named":/^[a-zA-Z0-9_]+$/,
                    "ignore":/^ignored:/
                };

            this.build = function (base, key, value) {
                base[key] = (value.indexOf('json:') == -1) ? value : $.evalJSON(value.substring(5));
                return base;
            };

            this.push_counter = function (key, i) {
                if (push_counters[key] === undefined) {
                    push_counters[key] = 0;
                }
                if (i === undefined) {
                    return push_counters[key]++;
                } else if (i !== undefined && i > push_counters[key]) {
                    return push_counters[key] = ++i;
                }
            };

            $.each($(this).serializeArray(), function () {
                // skip invalid keys
                if (!patterns.validate.test(this.name) || patterns.ignore.test(this.name)) {
                    return;
                }

                var k, keys = this.name.match(patterns.key),
                    merge = this.value,
                    reverse_key = this.name;

                while ((k = keys.pop()) !== undefined) {
                    // adjust reverse_key
                    reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');
                    // push
                    if (k.match(patterns.push)) {
                        merge = self.build([], self.push_counter(reverse_key), merge);
                    }
                    // fixed
                    else if (k.match(patterns.fixed)) {
                        self.push_counter(reverse_key, k);
                        merge = self.build([], k, merge);
                    }
                    // named
                    else if (k.match(patterns.named)) {
                        merge = self.build({}, k, merge);
                    }
                }

                json = $.extend(true, json, merge);
            });

            return json;
        };
        return JSNVisualDesign;
    });