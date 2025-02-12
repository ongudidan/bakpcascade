/*------------------------------------------------------------------------
 # Full Name of JSN UniForm
 # ------------------------------------------------------------------------
 # author    JoomlaShine.com Team
 # copyright Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 # Websites: http://www.joomlashine.com
 # Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 # @license - GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 # @version $Id: form.js 19014 2012-11-28 04:48:56Z thailv $
 -------------------------------------------------------------------------*/
define([
    'jquery',
    'uniform/visualdesign/visualdesign',
    'uniform/uniform',
    'uniform/help',
    'uniform/dialogedition',
    'uniform/libs/colorpicker/js/colorpicker',
    'jsn/libs/modal',
    'jquery.json',
    'jquery.zeroclipboard',
    'jquery.ui'],

    function ($, JSNVisualDesign, JSNUniform, JSNHelp, JSNUniformDialogEdition) {
        var JSNUniformFormView = function (params) {
            this.params = params;
            this.lang = params.language;
            this.formStyle = params.form_style;
            this.urlAction = params.urlAction;
            this.checkSubmitModal = params.checkSubmitModal;
            this.baseZeroClipBoard = params.baseZeroClipBoard;
            this.pageContent = params.pageContent;
            this.opentArticle = params.opentArticle;
            this.titleForm = params.titleForm;
            this.init();
        }
        var oldValuePage = $("#form-design-header").attr('data-value');
        JSNUniformFormView.prototype = {
            init:function () {
                var self = this;
                this.visualDesign = new JSNVisualDesign('#form-container', this.params);
                this.JSNUniform = new JSNUniform(this.params, this.visualDesign);
                this.JSNHelp = new JSNHelp();
                this.selectPostAction = $("#jform_form_post_action");
                this.inputFormTitle = $("#jform_form_title");
                this.btnAddPageForm = $(".new-page");
                this.btnSelectFormStyle = $("#select_form_style");
                var idForm = $("#jform_form_id").val();
                this.menuToolBar = $("#jsn-menu-item-toolbar-menu .jsn-menu .dropdown-menu a");
                this.menuToolBar.click(function (e) {
                    if(!$(this).hasClass("dropdown-toggle")){
                        var selfLink = this;
                        $("#confirmSaveForm").remove();
                        $(this).after(
                            $("<div/>", {
                                "id":"confirmSaveForm"
                            }).append(
                                $("<div/>", {
                                    "class":"ui-dialog-content-inner jsn-bootstrap"
                                }).append(
                                    $("<p/>").append(self.lang['JSN_UNIFORM_CONFIRM_SAVE_FORM']))));
                        $("#confirmSaveForm").dialog({
                            height:200,
                            width:500,
                            title:"Confirm",
                            draggable:false,
                            resizable:false,
                            autoOpen:true,
                            modal:true,
                            buttons:{
                                Yes:function () {
                                    $(this).dialog("close");
                                    $("#redirectUrl").val($(selfLink).attr("href"));
                                    Joomla.submitbutton("form.apply");
                                    return false;
                                },
                                No:function () {
                                    $(this).dialog("close");
                                    window.location.href = $(selfLink).attr("href");
                                    return false;
                                }
                            }
                        });
                        return false;
                    }
                });
                $("a.action-save-show").click(function () {
                    $("#redirectUrlForm").val($(this).attr("href"));
                    Joomla.submitbutton("form.apply");
                    return false;
                });
                $("#dialog-plugin").dialog({
                    height:300,
                    width:500,
                    title:self.lang['JSN_UNIFORM_LAUNCHPAD_PLUGIN_SYNTAX'],
                    draggable:false,
                    resizable:false,
                    autoOpen:false,
                    modal:true,
                    buttons:{
                        Close:function () {
                            $(this).dialog("close");
                        }
                    }
                });
                if (this.opentArticle == "open") {
                    this.opentAcrtileContent();
                }
                $("#article-content-plugin").click(function () {
                    $("#open-article").val("open");
                    Joomla.submitbutton("form.apply");
                });

                $(".jsn-tabs").tabs({
                    selected:0
                });

                var formAction = self.params.formAction ? self.params.formAction : 0;
                if (formAction && formAction != 0) {
                    $("#jform_form_post_action option").each(function () {
                        if (formAction == $(this).val()) {
                            var formActionData = self.params.formActionData;
                            $(this).prop('selected', true);
                            if (formAction == 1 || formAction == 4) {
                                $("#form_post_action_data" + formAction).val(formActionData);
                            }
                            if (formAction == 2 || formAction == 3) {
                                $("#form_post_action_data" + formAction).val(formActionData.id);
                                $("#fr" + formAction + "_form_action_data_title").val(formActionData.title);
                            }
                        }
                    });
                }

                if ($("#jform_form_type").val() == 2) {
                    $(".jsn-master #form-design #form-design-header").show();
                } else {
                    $(".jsn-master #form-design #form-design-header").hide();
                }
                this.selectPostAction.change(function () {
                    $('#postaction div[id^=form]').addClass("hide");
                    $('#form' + $(this).val()).removeClass("hide");
                    $("#action").val($(this).val());
                }).change();
                $(".jsn-page-actions .prev-page").click(function () {
                    self.prevpaginationPage();
                });
                $(".jsn-page-actions .next-page").click(function () {
                    self.nextpaginationPage();
                });
                $("#jform_form_type").change(function () {
                    if ($(this).val() == 1) {
                        if (confirm(self.lang['JSN_UNIFORM_CONFIRM_CONVERTING_FORM'])) {
                            $(".jsn-master #form-design #form-design-header").hide();
                            var dataValue = $(".jsn-page-list > li.page-items").attr("data-value");
                            var dataText = $(".jsn-page-list > li.page-items > input").val();
                            $("#form-design-header").attr("data-value", dataValue);
                            $("#form-design-header .page-title h1").text(dataText);
                            self.loadPage('join');
                        } else {
                            $("#jform_form_type option").each(function () {
                                if ($(this).val() == 2) {
                                    $(this).prop('selected', true);
                                }
                            });
                        }
                    } else {
                        $(".jsn-master #form-design #form-design-header").show();
                    }
                });
                this.btnAddPageForm.click(function () {
                    self.addNewPage();
                });
                this.inputFormTitle.bind('keypress', function (e) {
                    if (e.keyCode == 13) {
                        return false;
                    }
                });

                //get menu item
                window.jsnGetSelectMenu = function (id, title, object, link) {
                    $("#form_post_action_data2").val(id);
                    $("#fr2_form_action_data_title").val(title);
                    $.closeModalBox();
                }
                // get article
                window.jsnGetSelectArticle = function (id, title, catid, object, link) {
                    $("#form_post_action_data3").val(id);
                    $("#fr3_form_action_data_title").val(title);
                    $.closeModalBox();
                }

                if (this.checkSubmitModal) {
                    window.parent.jQuery.getSetModal($("#jform_form_id").val());
                }
                window.parentSaveForm = function () {
                    $.parentSaveForm();
                }
                $.parentSaveForm = function () {
                    $(document).trigger("click");
                    var listOptionPage = [];
                    $(" ul.jsn-page-list li.page-items").each(function () {
                        listOptionPage.push([$(this).find("input").attr('data-id'), $(this).find("input").attr('value')]);
                    });
                    $.ajax({
                        type:"POST",
                        async:true,
                        url:"index.php?option=com_uniform&view=form&task=form.savepage&tmpl=component",
                        data:{
                            form_id:$("#jform_form_id").val(),
                            form_content:self.visualDesign.serialize(),
                            form_page_name:$("#form-design-header").attr('data-value'),
                            form_list_page:listOptionPage
                        },
                        success:function () {
                            if ($("#jform_form_title").val() == "") {
                                $(".jsn-tabs").tabs({
                                    selected:0
                                });
                                $("#jform_form_title").parent().parent().addClass("error");
                                $("#jform_form_title").focus();
                                alert('Please correct the errors in the Form');
                                return false;
                            } else {
                                $("#jsn-task").val("form.apply");
                                $("form#adminForm").submit();
                            }

                        }
                    });
                }
                this.checkPage();
                if (this.urlAction != "component") {
                    Joomla.submitbutton = function (pressedButton) {
                        var listOptionPage = [];
                        $(" ul.jsn-page-list li.page-items").each(function () {
                            listOptionPage.push([$(this).find("input").attr('data-id'), $(this).find("input").attr('value')]);
                        });
                        $.ajax({
                            type:"POST",
                            async:true,
                            url:"index.php?option=com_uniform&view=form&task=form.savepage&tmpl=component",
                            data:{
                                form_id:$("#jform_form_id").val(),
                                form_content:self.visualDesign.serialize(),
                                form_page_name:$("#form-design-header").attr('data-value'),
                                form_list_page:listOptionPage
                            },
                            success:function () {
                                if (/^form\.(save|apply)/.test(pressedButton)) {
                                    if ($("#jform_form_title").val() == "") {
                                        $(".jsn-tabs").tabs({
                                            selected:0
                                        });
                                        $("#jform_form_title").parent().parent().addClass("error");
                                        $("#jform_form_title").focus();
                                        alert('Please correct the errors in the Form');
                                        return false;
                                    }
                                }
                                submitform(pressedButton);
                            }
                        });
                    };
                }
                $("#form-design-header a.element-edit").click(function () {
                    self.cerateEditPage($(this));
                });
                $("#form-design-header a.element-delete").click(function (e) {
                    self.removePage(this);
                    e.stopPropagation();
                });
                $(".jsn-modal-overlay,.jsn-modal-indicator").remove();
                $("body").append($("<div/>", {
                    "class":"jsn-modal-overlay",
                    "style":"z-index: 1000; display: inline;"
                })).append($("<div/>", {
                    "class":"jsn-modal-indicator",
                    "style":"display:block"
                })).addClass("jsn-loading-page");
                this.loadPage('defaultPage');
                this.actionForm();
                if (this.titleForm) {
                    $("#jform_form_title").val(this.titleForm);
                }
                this.btnSelectFormStyle.click(function (e) {
                    self.dialogFormStyle($(this));
                    e.stopPropagation();
                });
                $("#form-design-content").attr("class", $("#jform_form_theme").val());
                $("#jform_form_style").change(function () {
                    if ($(this).val() == "form-horizontal") {
                        $("#form-design-content").addClass("form-horizontal");
                    } else {
                        $("#form-design-content").removeClass("form-horizontal");
                    }
                    //$("#form-design-content").attr("class",$(this).val())
                }).trigger("change")
                self.changeTheme();
                if (!idForm) {
                    $("#jform_form_theme").trigger("change");
                }
                $("#jform_form_edit_submission0,#jform_form_edit_submission1").change(function () {
                    if ($("#jform_form_edit_submission1").is(':checked')) {
                        $("#jsn-select-user-group").removeClass("hide");
                    } else {
                        $("#jsn-select-user-group").addClass("hide");
                    }
                }).trigger("change");
            },
            changeTheme:function () {
                $("#jform_form_theme").change(function () {
                    $("#style_accordion_content input").val("");
                    $("#form-design-content").attr("class", $(this).val());
                    if ($(this).val() == "jsn-style-light") {
                        $("#style_background_active_color").val("#FCF8E3");
                        $("#style_border_active_color").val("#FBEED5");
                        $("#style_text_color").val("#333333");
                        $("#style_font_size").val("14");
                        $("#style_message_error_text_color").val("#FFFFFF");
                        $("#style_message_error_background_color").val("#B94A48");
                        $("#style_field_background_color").val("#ffffff");
                        $("#style_field_shadow_color").val("");
                        $("#style_field_text_color").val("#666666");
                        $("#style_field_border_color").val("");
                        $("#style_padding_space").val(10);
                        $("#style_margin_space").val(0);
                        $("#style_border_thickness").val(0);
                        $("#style_rounded_corner_radius").val(0);

                    } else if ($(this).val() == "jsn-style-dark") {
                        $("#style_background_active_color").val("#444444");
                        $("#style_border_active_color").val("#666666");
                        $("#style_text_color").val("#C6C6C6");
                        $("#style_font_size").val("14");
                        $("#style_message_error_text_color").val("#FFFFFF");
                        $("#style_message_error_background_color").val("#B94A48");
                        $("#style_field_background_color").val("#000000");
                        $("#style_field_shadow_color").val("#000000");
                        $("#style_field_text_color").val("#333333");
                        $("#style_field_border_color").val("#111111");
                        $("#style_padding_space").val(10);
                        $("#style_margin_space").val(0);
                        $("#style_border_thickness").val(0);
                        $("#style_rounded_corner_radius").val(0);
                    }
                    $(".jsn-select-color").each(function () {
                        var inputParent = $(this).prev();
                        $(this).find("div").css("background-color", $(inputParent).val());
                        $(this).ColorPickerSetColor($(inputParent).val());
                    });
                    $("#style_accordion_content input").trigger("change");
                });
            },
            hexToRgb:function (h) {
                var r = parseInt((this.cutHex(h)).substring(0, 2), 16), g = ((this.cutHex(h)).substring(2, 4), 16), b = parseInt((this.cutHex(h)).substring(4, 6), 16)
                return r + ',' + b + ',' + b;
            },
            cutHex:function (h) {
                return (h.charAt(0) == "#") ? h.substring(1, 7) : h
            },
            changeStyleInline:function () {
                var self = this,
                    styleField = ".jsn-master #form-design-content .form-region .form-element .controls input,.jsn-master #form-design-content .form-region .form-element .controls select,.jsn-master #form-design-content .form-region .form-element .controls textarea{\n",
                    styleFormElement = ".jsn-master #form-design-content .form-region .form-element {\n",
                    styleActive = ".jsn-master #form-design-content .form-region .form-element.ui-state-edit {\n",
                    styleTitle = ".jsn-master #form-design-content .form-region .form-element .control-label {\n";
                $("#style_accordion_content input").each(function () {
                    var dataValue = $(this).attr("data-value");
                    var valueInput = $(this).val();
                    if (valueInput) {
                        if ($(this).attr("type") == "number") {
                            if (dataValue == "border") {
                                valueInput = valueInput + "px solid";
                            } else if (dataValue == "margin") {
                                valueInput = valueInput + "px 0px";
                            } else {
                                valueInput = valueInput + "px";
                            }
                        }
                        var dataType = $(this).attr("data-type");
                        switch (dataType) {
                            case "form-element":
                                if (dataValue) {
                                    var items = dataValue.split(",");
                                    if (items.length > 1) {
                                        $.each(items, function (value, key) {
                                            styleFormElement += key + ":" + valueInput + ";\n";
                                        });
                                    } else {
                                        styleFormElement += items + ":" + valueInput + ";\n";
                                    }
                                }
                                break;
                            case "ui-state-edit":
                                styleActive += dataValue + ":" + valueInput + ";\n";
                                break;
                            case "control-label":
                                styleTitle += dataValue + ":" + valueInput + ";\n";
                                break;
                            case "field":
                                if (dataValue == "background-color") {
                                    styleField += "background:" + valueInput + ";\n";
                                } else if (dataValue == "box-shadow") {
                                    valueInput = self.hexToRgb(valueInput);
                                    styleField += "box-shadow:0 1px 0 rgba(255, 255, 255, 0.1), 0 1px 7px 0 rgba(" + valueInput + ",0.8) inset;\n";
                                } else {
                                    styleField += dataValue + ":" + valueInput + ";\n";
                                }
                                break;
                        }
                    }
                });
                styleFormElement += "}\n";
                styleActive += "}\n";
                styleTitle += "}\n";
                styleField += "}\n";
                $("#style_inline").html("<style>" + styleFormElement + styleActive + styleTitle + styleField + "</style>");
            },
            dialogFormStyle:function (_this) {
                var self = this;
                var dialog = $("#container-select-style"), parentDialog = $("#container-select-style").parent();
                dialog.width("500");
                $(dialog).appendTo('body');
                var elmStyle = JSNVisualDesign.getBoxStyle($(dialog)),
                    parentStyle = JSNVisualDesign.getBoxStyle($(_this)),
                    position = {};
                position.left = parentStyle.offset.left - elmStyle.outerWidth + parentStyle.outerWidth;
                //position.left = parentStyle.offset.left + (parentStyle.outerWidth - elmStyle.outerWidth) / 2;
                position.top = parentStyle.offset.top + parentStyle.outerHeight;
                $(dialog).find(".arrow").css("left", elmStyle.outerWidth - (parentStyle.outerWidth / 2));
                dialog.css(position).click(function (e) {
                    e.stopPropagation();
                });
                $(".jsn-select-color").each(function () {
                    var inputParent = $(this).prev();
                    var selfColor = this;
                    $(this).find("div").css("background-color", $(inputParent).val());
                    $(this).ColorPicker({
                        color:$(inputParent).val(),
                        onChange:function (hsb, hex, rgb) {
                            $(selfColor).prev().val("#" + hex);
                            var idInput = $(selfColor).prev().attr("id");
                            $(selfColor).find("div").css("background-color", "#" + hex);
                            self.changeStyleInline();
                        }
                    });
                });
                $("#style_accordion_content input").change(function () {
                    self.changeStyleInline();
                });
                $(dialog).show();
                $("#container-select-style .popover").show();
                /*
                 var availableTags = [
                 " Verdana, Geneva, sans-serif",
                 "\"Times New Roman\", Times, serif",
                 "\"Courier New\", Courier, monospace",
                 "Tahoma, Geneva, sans-serif",
                 "Arial, Helvetica, sans-serif",
                 "\"Trebuchet MS\", Arial, Helvetica, sans-serif",
                 "\"Arial Black\", Gadget, sans-serif",
                 "\"Lucida Sans Unicode\", \"Lucida Grande\", sans-serif",
                 "\"Palatino Linotype\", \"Book Antiqua\", Palatino, serif",
                 "\"Comic Sans MS\", cursive"
                 ];
                 $("#style_font_type").autocomplete({
                 source:availableTags,
                 minLength:0
                 }).click(function () {
                 $(this).autocomplete("search");
                 });
                 */
                $(".jsn-input-number").keypress(function (e) {
                    if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                        return false;
                    }
                });
                $(document).click(function (e) {
                    if ($(e.target).parents(".colorpicker").css('display') == 'block' || ( $(e.target).hasClass("colorpicker") && $(e.target).css('display') == 'block')) {
                        return false;
                    }
                    if ($(e.target).parent().parent().hasClass("ui-autocomplete")) {
                        return false;
                    }
                    if ($(e.target).parents(".ui-autocomplete").css('display') == 'block') {
                        return false;
                    }
                    if ($(dialog).css('display') != 'none') {
                        $(dialog).appendTo($(parentDialog));
                        dialog.hide();
                        dialog.width("0");
                    }
                });
            },
            actionForm:function () {
                var self = this;
                $(".form-actions  .jsn-iconbar a.element-edit").click(function () {
                    var sender = $(this).parents(".form-actions");
                    $(sender).addClass("ui-state-edit");
                    var type = "form-actions";
                    var params = {};
                    var action = $(this);
                    JSNVisualDesign.openOptionsBox(sender, type, params, action);
                    $("#option-btnNext-text").val($("#jform_form_btn_next_text").val()).keyup(function () {
                        var btnNext = $("#option-btnNext-text").val() ? $("#option-btnNext-text").val() : "Next";
                        $("#jform_form_btn_next_text").val(btnNext);
                        $(".form-actions .btn-toolbar .jsn-form-next").text(btnNext);
                    });
                    $("#option-btnPrev-text").val($("#jform_form_btn_prev_text").val()).keyup(function () {
                        var btnPrev = $("#option-btnPrev-text").val() ? $("#option-btnPrev-text").val() : "Prev";
                        $("#jform_form_btn_prev_text").val(btnPrev);
                        $(".form-actions .btn-toolbar .jsn-form-prev").text(btnPrev);
                    });
                    $("#option-btnSubmit-text").val($("#jform_form_btn_submit_text").val()).keyup(function () {
                        var btnSubmit = $("#option-btnSubmit-text").val() ? $("#option-btnSubmit-text").val() : "Submit";
                        $("#jform_form_btn_submit_text").val(btnSubmit);
                        $(".form-actions .btn-toolbar .jsn-form-submit").text(btnSubmit);
                    });
                });
                $(".settings-footer .jsn-iconbar a.element-delete").click(function () {
                    self.JSNUniformDialogEdition = new JSNUniformDialogEdition(self.params);
                    JSNUniformDialogEdition.createDialogLimitation($(this), self.lang["JSN_UNIFORM_YOU_CAN_NOT_HIDE_THE_COPYLINK"]);
                    return false;
                });
            },
            //get data page
            loadPage:function (action) {
                var self = this;
                var listOptionPage = [];

                $(" ul.jsn-page-list li.page-items").each(function () {
                    listOptionPage.push([$(this).find("input").attr('data-id'), $(this).find("input").attr('value')]);
                });
                $("#form-design-content #page-loading").show();
                $("#form-design-content .form-row ").hide();
                $(".jsn-page-actions").hide();
                $("#form-design-header .jsn-iconbar").css("display", "none");
                $.ajax({
                    type:"POST",
                    dataType:'json',
                    url:"index.php?option=com_uniform&view=form&task=form.loadpage&tmpl=component",
                    data:{
                        form_page_name:$("#form-design-header").attr('data-value'),
                        form_page_old_name:oldValuePage,
                        form_page_old_content:self.visualDesign.serialize(),
                        form_id:$("#jform_form_id").val(),
                        form_list_page:listOptionPage,
                        join_page:action
                    },
                    success:function (response) {
                        if ($("#jform_form_id").val() > 0 && self.pageContent) {
                            var pageContent = JSON.parse(self.pageContent);
                            if (!response && action == "defaultPage" && $.inArray(oldValuePage, pageContent) != -1) {
                                location.reload();
                            }
                        }
                        self.visualDesign.clearElements();
                        self.visualDesign.setElements(response);
                        if (action == "join") {
                            $(".jsn-page-list li.page-items").each(function (index) {
                                if (index != 0) {
                                    $(this).remove();
                                }
                            });
                            self.checkPage();
                        }
                        if (action == 'defaultPage') {
                            JSNVisualDesign.emailNotification();
                            $("#adminForm").removeClass("hide");
                            $(".jsn-modal-overlay,.jsn-modal-indicator").remove();
                        }
                        $(".jsn-page-actions").show();
                        $("#form-design-content #page-loading").hide();
                        $("body").removeClass("jsn-loading-page");
                        $("#form-design-content .form-row ").show();
                        $("#form-design-header .jsn-iconbar").css("display", "");
                    }
                });
                oldValuePage = $("#form-design-header").attr('data-value');
            },
            //Add new Page
            addNewPage:function () {
                JSNVisualDesign.savePage();
                this.visualDesign.clearElements();
                var randomMath = Math.floor((Math.random() * 100000000) + 10000);
                var countSelect = $("ul.jsn-page-list li.page-items").size() + 1;
                var selectAdd = "<li id='new_" + randomMath + "' data-value='" + randomMath + "' class=\"page-items\"><a href=\"#\">Page " + countSelect + "</a><input type=\"hidden\" value=\"Page " + countSelect + "\" data-id=\"" + randomMath + "\" name=\"name_page[" + randomMath + "]\"/></li>";
                $("ul.jsn-page-list").append(selectAdd);
                $("#form-design #form-design-header").attr("data-value", $("#new_" + randomMath).attr("data-value"));
                $("#form-design #form-design-header .page-title h1").text($("#new_" + randomMath).find("input").val());
                oldValuePage = $("#form-design-header").attr('data-value');
                this.checkPage();
                $("#form-design-header .icon-pencil").trigger("click")

            },
            //create edit page
            cerateEditPage:function (_this) {
                var item = $(_this).parent().parent().parent();

                $("#form-design-header .jsn-page-actions").hide();
                $("#form-design-header .page-edit-form").remove();
                var self = this;
                item.find(".page-title").hide();
                $("#form-design-header").addClass("edit-page-item").append(
                    $("<div/>", {
                        "class":"page-edit-form form-inline"
                    }).append(
                        $("<input>", {
                            'type':'text',
                            'value':item.find("h1").text(),
                            'class':'page-input-tmp input-xlarge'
                        })).append(
                        $("<button/>", {
                            "onclick":"return false;",
                            "class":"btn btn-icon save-page"
                        }).append(
                            $("<i/>", {
                                "class":"icon-ok"
                            })).click(function () {
                                self.saveEditPage();
                                return false;
                            })).append(
                        $("<button/>", {
                            "onclick":"return false;",
                            "class":"btn btn-icon cancel-page"
                        }).append(
                            $("<i/>", {
                                "class":"icon-remove"
                            })).click(function () {
                                self.cancelEditPage();
                                return false;
                            })))
                $(" .edit-page-item .page-input-tmp").focus().bind('keypress', function (e) {
                    if (e.keyCode == 13) {
                        self.saveEditPage();
                        return false;
                    }
                    if (e.keyCode == 27) {
                        self.cancelEditPage();
                    }
                });
            },
            //remove page
            removePage:function (_this) {
                var self = this;
                var liActive = $(_this).parent().parent().parent();
                var itemRemove = liActive.attr("data-value");
                if (confirm("Are you sure you want to delete page " + liActive.find("h3").text() + " with all fields?")) {
                    if ($("ul.jsn-page-list li.page-items").size() > 1) {
                        $("ul.jsn-page-list li.page-items").each(function () {
                            if ($(this).attr("data-value") == itemRemove) {
                                if ($(this).next().attr("data-value")) {
                                    $("#form-design #form-design-header").attr("data-value", $(this).next().attr("data-value"));
                                    $("#form-design #form-design-header .page-title h1").html($(this).next().find("input").val());
                                    $(this).remove();

                                } else if ($(this).prev().attr("data-value")) {
                                    $("#form-design #form-design-header").attr("data-value", $(this).prev().attr("data-value"));
                                    $("#form-design #form-design-header .page-title h1").html($(this).prev().find("input").val());
                                    $(this).remove();

                                }
                            }
                        });
                        $("#form-design-content #page-loading").show();
                        $("#form-design-content .form-row ").hide();
                        $.ajax({
                            type:"POST",
                            dataType:'json',
                            url:"index.php?option=com_uniform&view=form&task=form.loadpage&tmpl=component",
                            data:{
                                form_id:$("#jform_form_id").val(),
                                form_page_name:$("#form-design-header").attr('data-value'),
                                form_page_old_name:oldValuePage,
                                form_page_old_content:this.visualDesign.serialize()
                            },
                            success:function (response) {
                                self.visualDesign.clearElements();
                                self.visualDesign.setElements(response);
                                JSNVisualDesign.savePage();
                                $("#form-design-content #page-loading").hide();
                                $("#form-design-content .form-row ").show();
                            }
                        });

                        self.checkPage();
                        oldValuePage = $("#form-design-header").attr('data-value');
                    }

                }
            },
            //cancel edit page
            cancelEditPage:function () {
                var editPageItem = $(".edit-page-item");
                editPageItem.find(".page-title").show();
                editPageItem.find(".page-edit-form").hide();
                editPageItem.removeClass("edit-page-item");
                this.checkPage();

            },
            //save edit page
            saveEditPage:function (e) {
                var self = this;
                var inputTmpPage = $(".edit-page-item .page-input-tmp");
                if (inputTmpPage.val() != "") {
                    $("ul.jsn-page-list li.page-items input").each(function () {
                        if ($(this).attr("data-id") == $("#form-design-header").attr('data-value')) {
                            $(this).val(inputTmpPage.val());
                            $(this).prev().text(inputTmpPage.val());
                            $("#form-design-header .page-title h1").text(inputTmpPage.val());
                        }
                    });
                    JSNVisualDesign.savePage();
                    self.cancelEditPage();
                    self.checkPage();
                } else {
                    $(".page-input-tmp").addClass("error");
                    if (e) {
                        e.stopPropagation();
                    }
                }
            },
            loadDefaultPage:function (value) {
                var self = this;
                $("ul.jsn-page-list li.page-items").each(function () {
                    if ($(this).attr("data-value") == value) {
                        var dataValue = $(this).attr("data-value");
                        var dataText = $(this).find("input").val();
                        $("#form-design-header").attr("data-value", dataValue);
                        $("#form-design-header .page-title h1").text(dataText);
                        return false;
                    }
                });
                self.loadPage('defaultPage');

            },
            nextpaginationPage:function () {
                var self = this;
                $("ul.jsn-page-list li.page-items").each(function () {
                    if ($(this).attr("data-value") == $("#form-design-header").attr("data-value")) {
                        var dataValue = $(this).next().attr("data-value");
                        var dataText = $(this).next().find("input").val();
                        $("#form-design-header").attr("data-value", dataValue);
                        $("#form-design-header .page-title h1").text(dataText);
                        return false;
                    }
                });
                self.checkPage();
                self.loadPage();
            },
            prevpaginationPage:function () {
                var self = this;
                $("ul.jsn-page-list li.page-items").each(function () {
                    if ($(this).attr("data-value") == $("#form-design-header").attr("data-value")) {
                        var dataValue = $(this).prev().attr("data-value");
                        var dataText = $(this).prev().find("input").val();
                        $("#form-design-header").attr("data-value", dataValue);
                        $("#form-design-header .page-title h1").text(dataText);
                    }
                });
                self.checkPage();
                self.loadPage();
            },
            //check count page
            checkPage:function () {
                var self = this;
                $("#form-design-header .jsn-page-actions").show();
                var pageItems = $("ul.jsn-page-list li.page-items");
                if (pageItems.size() <= 1) {
                    $("#form-design-header a.element-delete").hide();
                    $(".form-actions .btn-toolbar .jsn-form-submit").removeClass("hide");
                } else {
                    $("#form-design-header a.element-delete").show();
                    $(".form-actions .btn-toolbar .jsn-form-submit").addClass("hide");
                }
                pageItems.each(function () {
                    if ($(this).attr("data-value") == $("#form-design-header").attr("data-value")) {
                        if ($(this).next().attr("data-value")) {
                            $(".jsn-page-actions .next-page").removeAttr("disabled");
                            $(".form-actions .btn-toolbar .jsn-form-next").removeClass("hide");
                        } else {
                            $(".jsn-page-actions .next-page").attr("disabled", "disabled");
                            $(".form-actions .btn-toolbar .jsn-form-next").addClass("hide");
                        }
                        if ($(this).prev().attr("data-value")) {
                            $(".jsn-page-actions .prev-page").removeAttr("disabled");
                            $(".form-actions .btn-toolbar .jsn-form-prev").removeClass("hide");
                        } else {
                            $(".jsn-page-actions .prev-page").attr("disabled", "disabled");
                            $(".form-actions .btn-toolbar .jsn-form-prev").addClass("hide");
                        }
                        if ($(this).prev().attr("data-value") && !$(this).next().attr("data-value") || !$(this).next().attr("data-value") && !$(this).next().attr("data-value")) {
                            $(".form-actions .btn-toolbar .jsn-form-submit").removeClass("hide");
                        } else {
                            $(".form-actions .btn-toolbar .jsn-form-submit").addClass("hide");
                        }
                    }
                });
            },
            opentAcrtileContent:function () {

                var self = this;
                var valuePlugin = "{uniform form=" + $("#jform_form_id").val() + "/}";
                $("#syntax-plugin").val(valuePlugin);
                $("#dialog-plugin").dialog("open");
                ZeroClipboard.moviePath = self.baseZeroClipBoard;
                var clipboard = new ZeroClipboard.Client();
                clipboard.glue('jsn-clipboard-button', 'dialog-plugin', {
                    "z-index":"9999"
                });
                clipboard.setText($("#syntax-plugin").val());
                $("#syntax-plugin").change(function () {
                    clipboard.setText($("#syntax-plugin").val());
                });
                clipboard.addEventListener('complete', function (client, text) {
                    if ($("#syntax-plugin").val() != '') {
                        $(".jsn-clipboard-checkicon").addClass('jsn-clipboard-coppied');
                        setTimeout(function () {
                            $(".jsn-clipboard-checkicon").delay(6000).removeClass('jsn-clipboard-coppied');
                        }, 2000);
                    }
                });
            }
        }
        return JSNUniformFormView;
    });