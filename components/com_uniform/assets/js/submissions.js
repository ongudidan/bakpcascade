/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
define([
    'jquery',
    'jquery.daterangepicker',
    'jquery.ui'],

    function ($, JSNHelp) {
        var JSNUniformSubmissionsView = function (params) {
            this.params = params;
            this.lang = params.language;
            this.init();
        }
        JSNUniformSubmissionsView.prototype = {
            init: function () {

                var self = this;

                $("#submission-fields-list").hide();
                $(".jsn-items-list").sortable({
                    items: "div:not(.field-disabled)"
                });

                $('button.select-field').click(function (e) {
                    self.dialogSelectFields($(this));
                    e.stopPropagation();
                });
                if ($("#filter_date_submission").length) {
                    var presetRanges = [];
                    var presets = {};
                    presetRanges.push({
                        text: 'Today',
                        dateStart: 'today',
                        dateEnd: 'today'
                    });
                    presetRanges.push({
                        text: 'Last 7 days',
                        dateStart: 'today-7days',
                        dateEnd: 'today'
                    });
                    presetRanges.push({
                        text: 'The previous Month',
                        dateStart: function () {
                            return Date.parse('1 month ago').moveToFirstDayOfMonth();
                        },
                        dateEnd: function () {
                            return Date.parse('1 month ago').moveToLastDayOfMonth();
                        }
                    });
                    presets.specificDate = 'Specific Date';
                    presets.dateRange = 'Date Range';
                    $("#filter_date_submission").daterangepicker({
                        presetRanges: presetRanges,
                        presets: presets
                    });
                }
                $("#filter_form_id").change(function () {
                    $("form[name=adminForm] input[type=text],form[name=adminForm] select").attr("disabled", "disabled");
                });
            },
            dialogSelectFields: function (_this) {

                var self = this;
                var dialog = $("#submission-fields-list"),parentDialog = $("#submission-fields-list").parent();
                $(dialog).appendTo('body');
                dialog.show();
                $("#submission-fields-list .popover").show();
                var elmStyle = self.getBoxStyle($(dialog)),
                    parentStyle = self.getBoxStyle($(_this)),
                    position = {};
                position.left = parentStyle.offset.left - elmStyle.outerWidth + parentStyle.outerWidth;
                position.top = parentStyle.offset.top + parentStyle.outerHeight;
                $(dialog).find(".arrow").css("left", elmStyle.outerWidth - (parentStyle.outerWidth / 2));
                dialog.css(position).click(function (e) {
                    e.stopPropagation();
                });
                $("#done").click(function () {
                    if ($(dialog).css('display') != 'none') {
                        $(dialog).appendTo($(parentDialog));
                        dialog.hide();
                    }
                    var field = [];
                    var list_fields = [];
                    $('input:checkbox[name="field[]"]:checked').each(function (index) {
                        field.push('"' + $(this).val() + '"');
                    });
                    $('input:checkbox[name="field[]"]').each(function (index) {
                        list_fields.push($(this).val());
                    });
                    $('#list_view_field').val(field);
                    $('#filter_position_field').val(list_fields);

                    $("#adminForm").submit();
                });
                $(document).click(function () {
                    if ($(dialog).css('display') != 'none') {
                        $(dialog).appendTo($(parentDialog));
                        dialog.hide();
                    }
                });
            },
            getBoxStyle: function (element) {
                var style = {
                    width: element.width(),
                    height: element.height(),
                    outerHeight: element.outerHeight(),
                    outerWidth: element.outerWidth(),
                    offset: element.offset(),
                    margin: {
                        left: parseInt(element.css('margin-left')),
                        right: parseInt(element.css('margin-right')),
                        top: parseInt(element.css('margin-top')),
                        bottom: parseInt(element.css('margin-bottom'))
                    },
                    padding: {
                        left: parseInt(element.css('padding-left')),
                        right: parseInt(element.css('padding-right')),
                        top: parseInt(element.css('padding-top')),
                        bottom: parseInt(element.css('padding-bottom'))
                    }
                };
                return style;
            }
        }
        return JSNUniformSubmissionsView;
    });