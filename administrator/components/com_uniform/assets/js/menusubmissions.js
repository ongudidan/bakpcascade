/**
 * Description for file (if any)...
 *
 * @category Functions
 * @package Unifom
 * @author JoomlaShine.com
 * @copyright JoomlaShine.com
 * @license GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 * @version $Id:
 * @link JoomlaShine.com
 */
define([
    'jquery',
    'jquery.ui'],

    function ($) {
        var JSNUniformMenuSubmissionsView = function (params) {
            this.params = params;
            this.lang = params.language;
            this.value = params.value;
            this.name = params.name;
            this.init();
        }
        JSNUniformMenuSubmissionsView.prototype = {
            init:function () {
                var self = this;
                var form = $('select.jform_request_form_id');
                form.change(function () {

                    if (form.val() == 0) {
                        $(this).css("background", "#CC0000").css("color", "#fff")
                        $('#form-icon-warning').css('display', '');
                        $('#select-forms').attr("disabled", "disabled");
                        $("#page-loading").addClass("hide");
                        $("#form_field").addClass("hide");
                        $("#jform_params_field-lbl").addClass("hide");
                    } else {
                        $('#select-forms').removeAttr("disabled");
                        form.css("background", "#FFFFDD").css("color", "#000")
                        $('#form-icon-warning').css('display', 'none');
                        $("#page-loading").removeClass("hide");
                        $("#form_field").addClass("hide");
                        $("#jform_params_field-lbl").removeClass("hide");
                        self.loadFieldByForm(form.val());
                    }
                }).trigger("change");


            },
            loadFieldByForm:function (formId) {
                var self = this;
                $.ajax({
                    type:"GET",
                    async:true,
                    url:"index.php?option=com_uniform&view=forms&task=forms.getListFieldByForm&form_id=" + formId,
                    dataType:'json',
                    success:function (response) {
                        if (response) {
                            var html = "", name = {}, type = {}, identifier = [], field_identifier = [], identifierDisabled = [];
                            $.each(response, function () {
                                var val = "sb_" + $(this)[0].field_id;
                                identifier.push(val);
                                name[val] = $(this)[0].field_title;
                                type[val] = $(this)[0].field_type;
                            });
                            identifierDisabled.push('data_ip');
                            identifierDisabled.push('data_country');
                            identifierDisabled.push('data_browser');
                            identifierDisabled.push('data_os');
                            identifierDisabled.push('data_created_by');
                            identifierDisabled.push('data_created_at');
                            identifierDisabled.push('data_id');
                            identifier = $.merge(identifier, identifierDisabled);
                            name.data_ip = "IP Address";
                            name.data_country = "Country";
                            name.data_browser = "Browser";
                            name.data_os = "Operating System";
                            name.data_created_by = "Created By";
                            name.data_created_at = "Date Created";
                            name.data_id = "ID";
                            if (self.value) {
                                $.each(self.value.field_identifier, function (i, item) {
                                    if ($.inArray(item, identifier) != -1) {
                                        field_identifier.push(item);
                                    }
                                });
                            }
                            if (self.value.field_identifier) {
                                var identifierFilter = $.merge(field_identifier, identifier);
                                var listIdentifier = identifierFilter.filter(function (itm, i, identifierFilter) {
                                    return i == identifierFilter.indexOf(itm);
                                });
                            } else {
                                var listIdentifier = identifier;
                            }
                            $.each(listIdentifier, function (i, value) {
                                var checkbox = "";
                                if ($.inArray(value, self.value.field_view) != -1) {
                                    checkbox = "checked=\"true\"";
                                }
                                var typeField = type[value]?type[value]:"text";
                                html += "<div class=\"field jsn-item ui-state-default\"><label class=\"checkbox\"><input type=\"hidden\" value=\"" + value + "\" name=\"" + self.name + "[field_identifier][]\" ><input type=\"checkbox\" " + checkbox + " value=\"" + value + "\" name=\"" + self.name + "[field_view][]\" title=\"" + name[value] + "\" >" + name[value] + "</label></div>";
                            });
                            $("#page-loading").addClass("hide");
                            $("#page-loading").addClass("hide");
                            if (html) {
                                $("#form_field").html(html);
                                $(".no-filed").addClass("hide");
                                $("#form_field").removeClass("hide");
                            } else {
                                $(".no-filed").removeClass("hide");
                                $("#form_field").addClass("hide");
                            }
                            $(".jsn-items-list").sortable({
                                items:"div:not(.field-disabled)"
                            });
                        }
                    }
                });
            }
        }
        return JSNUniformMenuSubmissionsView;
    });