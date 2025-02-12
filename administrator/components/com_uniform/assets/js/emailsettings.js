/*------------------------------------------------------------------------
 # Full Name of JSN UniForm
 # ------------------------------------------------------------------------
 # author    JoomlaShine.com Team
 # copyright Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 # Websites: http://www.joomlashine.com
 # Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 # @license - GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 # @version $Id: emailsettings.js 14957 2012-08-10 11:47:52Z thailv $
 -------------------------------------------------------------------------*/
define([
    'jquery',
    'jquery.json',
    'jquery.tipsy',
    'uniform/libs/jquery.placeholder',
    'jquery.ui'],
    function ($) {
        var JSNUniformEmailSettingsView = function (params) {
            this.params = params;
            this.lang = params.language;
            this.init();
        }
        JSNUniformEmailSettingsView.prototype = {
            init:function () {
                $("#form-loading").hide();
                var self = this;
                var listOptionPage = [];
                var wordlist = [];
                //  this.btnSelectFieldFrom = $("#btn-select-field-from");
                // this.btnSelectFieldTo = $("#btn-select-field-to");
                //  this.btnSelectFieldSubject = $("#btn-select-field-subject");
                // this.btnSelectFieldMessage = $("#btn-select-field-message");
                $('.jsn-label-des-tipsy').tipsy({
                    gravity:'w',
                    fade:true
                });
                if (this.params.editor == 'none') {
                    $("#jform_template_message").css("width", "530px");
                }
                if (this.params.editor == 'jce') {
                    $("#btn-select-field-message").css("margin-top", "30px");
                }
                if ($("#template_notify_to").val() == 0) {
                    $("#jform_template_from").attr("placeholder", this.lang['JSN_UNIFORM_PLACEHOLDER_EMAIL_FROM_0']);
                    $("#jform_template_reply_to").attr("placeholder", this.lang['JSN_UNIFORM_PLACEHOLDER_EMAIL_REPLY_TO_0']);
                    $("#jform_template_subject").attr("placeholder", this.lang['JSN_UNIFORM_PLACEHOLDER_EMAIL_SUBJECT_0']);
                } else {
                    $("#jform_template_from").attr("placeholder", this.lang['JSN_UNIFORM_PLACEHOLDER_EMAIL_FROM_1']);
                    $("#jform_template_reply_to").attr("placeholder", this.lang['JSN_UNIFORM_PLACEHOLDER_EMAIL_REPLY_TO_1']);
                    $("#jform_template_subject").attr("placeholder", this.lang['JSN_UNIFORM_PLACEHOLDER_EMAIL_SUBJECT_1']);
                }
                parent.jQuery(" ul.jsn-page-list li.page-items").each(function () {
                    listOptionPage.push([$(this).find("input").attr('data-id'), $(this).find("input").attr('value')]);
                });
                $.ajax({
                    type:"POST",
                    dataType:'json',
                    url:"index.php?option=com_uniform&view=form&task=form.loadsessionfield&tmpl=component",
                    data:{
                        form_id:parent.jQuery("#jform_form_id").val(),
                        form_page_name:parent.jQuery("#form-design-header").attr('data-value'),
                        form_list_page:listOptionPage
                    },
                    success:function (response) {
                        var replyToSelect = "";
                        var liFields = "";
                        var typeClear = ["file-upload"];
                        if (response) {
                            $.each(response, function (i, item) {
                                if (item.type == 'email') {
                                    replyToSelect += "<div class=\"ui-state-default\" id='" + item.identify + "'><a href='javascript:void(0)'>" + item.options.label + "</a></div>";
                                }
                                liFields += "<div class=\"ui-state-default\" id='" + item.identify + "'><a href='javascript:void(0)'>" + item.options.label + "</a></div>";
                                wordlist.push(item.options.label);
                            });
                        }
                        if ($("#template_notify_to").val() == 1) {
                            self.createListField($("#btn-select-field-from"), liFields, "FIELD");
                            self.createListField($("#btn-select-field-to"), replyToSelect, "EMAIL");
                        }
                        self.createListField($("#btn-select-field-message"), liFields, "FIELD");
                        self.createListField($("#btn-select-field-subject"), liFields, "FIELD");
                    }
                });
                $('input, textarea').placeholder();
            },
            eventField:function (field, btnField, type) {
                var self = this;
                $(field).find(".jsn-items-list div.ui-state-default").click(function () {
                    if (this.id) {
                        switch (type) {
                            case "btn-select-field-message":
                                jInsertEditorText('{$' + this.id + '}', 'jform_template_message');
                                break;
                            case "btn-select-field-from":
                                $("#jform_template_from").val($("#jform_template_from").val() + "{$" + this.id + "}");
                                break;
                            case "btn-select-field-subject":
                                $("#jform_template_subject").val($("#jform_template_subject").val() + "{$" + this.id + "}");
                                break;
                            case "btn-select-field-to":
                                $("#jform_template_reply_to").val($("#jform_template_reply_to").val() + "{$" + this.id + "}");
                                break;
                        }
                        $("div.control-list-fields").hide();
                    }
                });
                $(btnField).click(function (e) {
                    $("div.control-list-fields").hide();
                    $(field).show();
                    e.stopPropagation();
                });
                $("div.control-list-fields").click(function (e) {
                    e.stopPropagation();
                });
                $(field).find(".search-field").keyup(function () {
                    self.searchField($(field).find(".jsn-items-list div.ui-state-default"), $(this).val());
                });
                $(document).click(function () {
                    $("div.control-list-fields").hide();
                });
            },
            // Search field in list
            searchField:function (divListField, value) {
                divListField.each(function () {
                    var textField = $(this).text().toLowerCase();
                    if (textField.search(value.toLowerCase()) == -1) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            },
            //Create list field
            createListField:function (btnInput, fields, type) {
                var self = this;
                var listField = fields;
                if (!fields) {
                    listField = "<div title=\"" + self.lang["JSN_UNIFORM_NO_" + type + "_DES"] + "\" class=\"ui-state-default ui-state-disabled\">" + self.lang["JSN_UNIFORM_NO_" + type] + "</div>"
                }
                var dialog = $("<div/>", {
                    'class':'control-list-fields jsn-bootstrap',
                    'id':"control-" + $(btnInput).attr("id")
                }).append(
                    $("<div/>", {
                        "class":"popover"
                    }).css("display", "block").append($("<div/>", {
                        "class":"arrow"
                    })).append($("<h3/>", {
                        "class":"popover-title",
                        "text":this.lang['JSN_UNIFORM_SELECT_FIELDS']
                    })).append(
                        $("<div/>", {
                            "class":"popover-content"
                        }).append(
                            $("<div/>", {
                                "class":"field-seach"
                            }).append($("<div/>", {
                                "class":"input-prepend"
                            }).append(
                                $("<span/>", {
                                    "class":"add-on btn-search"
                                }).append(
                                    $("<i/>", {
                                        "class":"icon-search pull-right"
                                    }))).append(
                                $("<input/>", {
                                    "type":"text",
                                    "class":"jsn-input-large-fluid search-field"
                                }).bind('keypress', function (e) {
                                        if (e.keyCode == 13) {
                                            return false;
                                        }
                                    })))).append(
                            $("<div/>", {
                                "class":"jsn-items-list ui-sortable"
                            }).append(listField)))

                )
                if (!fields) {
                    $(dialog).find(".field-seach").hide();
                } else {
                    $(dialog).find(".field-seach").show();
                }
                $(dialog).appendTo('body');
                var elmStyle = self.getBoxStyle($(dialog)),
                    parentStyle = self.getBoxStyle($(btnInput)),
                    position = {};
                position.left = parentStyle.offset.left - elmStyle.outerWidth + parentStyle.outerWidth;
                position.top = parentStyle.offset.top + parentStyle.outerHeight;
                $(dialog).find(".arrow").css("left", elmStyle.outerWidth - (parentStyle.outerWidth / 2));
                dialog.css(position).click(function (e) {
                    e.stopPropagation();
                });
                dialog.hide();
                self.eventField("#control-" + $(btnInput).attr("id"), btnInput, $(btnInput).attr("id"));
                $(document).click(function () {
                    dialog.hide();
                });
            },
            getBoxStyle:function (element) {
                var display = element.css('display')
                if (display == 'none') {
                    element.css({
                        display:'block',
                        visibility:'hidden'
                    });
                }
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
                element.css({
                    display:display,
                    visibility:'visible'
                });
                return style;
            }
        }
        return JSNUniformEmailSettingsView;
    });

function save() {
    //jQuery('.error').hide();
    jQuery("#uni-form").hide();
    jQuery("#form-loading").show();
    document.adminForm.submit();
    return false;
}