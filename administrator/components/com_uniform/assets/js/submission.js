/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
define([
    'jquery',
    'uniform/help'],

    function ($, JSNHelp) {
        var JSNUniformSubmissionView = function (params) {
            this.params = params;
            this.lang = params.language;
            this.nextAndPreviousForm = params.nextAndPreviousForm;
            this.init();
        }
        JSNUniformSubmissionView.prototype = {
            init:function () {
                var self = this;
                this.JSNHelp = new JSNHelp();
                $("#jsn-submission-edit").click(function () {
                    $(this).addClass("hide");
                    $("#jsn-submission-save").removeClass("hide");
                    $("#jsn-submission-cancel").removeClass("hide");
                    $("dl.submission-page-content").addClass("hide");
                    $("div.submission-page-content").removeClass("hide");
                });
                $("#jsn-submission-save").click(function () {
                    $(".submission-content .submission-page .submission-page-content input").each(function () {
                        var key = $(this).attr("dataValue");
                        var type = $(this).attr("typeValue");
                        $(this).attr("oldValue", $(this).val());
                        if (type != "email") {
                            $("dd#sb_" + key).html($(this).val());
                        } else {
                            if ($(this).val()) {
                                $("dd#sb_" + key + " a").html($(this).val());
                            } else {
                                $("dd#sb_" + key + " a").html("N/A");
                            }
                        }
                    });
                    $(".submission-content .submission-page .submission-page-content textarea").each(function () {
                        var key = $(this).attr("dataValue");
                        $(this).attr("oldValue", $(this).val());
                        if ($(this).val()) {
                            var value = $(this).val().split("\n");
                            $("dd#sb_" + key).html(value.join("<br/>"));
                        } else {
                            $("dd#sb_" + key).html("N/A");
                        }
                    });
                    $(this).addClass("hide");
                    $("#jsn-submission-cancel").addClass("hide");
                    $("#jsn-submission-edit").removeClass("hide");
                    $("dl.submission-page-content").removeClass("hide");
                    $("div.submission-page-content").addClass("hide");
                });
                /*
                $("#jsn-submission-cancel").click(function () {
                    $(".submission-content .submission-page .submission-page-content input").each(function () {
                        var key = $(this).attr("dataValue");
                        var type = $(this).attr("typeValue");
                        $(this).val($(this).attr("oldValue"));
                        if (type != "email") {
                            $("dd#sb_" + key).html($(this).val());
                        } else {
                            if ($(this).val()) {
                                $("dd#sb_" + key + " a").html($(this).val());
                            } else {
                                $("dd#sb_" + key + " a").html("N/A");
                            }
                        }
                    });
                    $(".submission-content .submission-page .submission-page-content textarea").each(function () {
                        var key = $(this).attr("dataValue");
                        $(this).val($(this).attr("oldValue"));
                        if ($(this).val()) {
                            var value = $(this).val().split("\n");
                            $("dd#sb_" + key).html(value.join("<br/>"));
                        } else {
                            $("dd#sb_" + key).html("N/A");
                        }
                    });
                    $(this).addClass("hide");
                    $("#jsn-submission-save").addClass("hide");
                    $("#jsn-submission-edit").removeClass("hide");
                    $("dl.submission-page-content").removeClass("hide");
                    $("div.submission-page-content").addClass("hide");
                });
                */
                $(".jsn-page-actions .prev-page").click(function () {
                    self.prevpaginationPage();
                });
                $(".jsn-page-actions .next-page").click(function () {
                    self.nextpaginationPage();
                });
                $("#jform_form_type option").each(function () {
                    if ($(this).val() == $("#jform_form_type").attr("data-value")) {
                        $(this).prop("selected", true);
                    } else {
                        $(this).prop("selected", false);
                    }
                });
                if (this.nextAndPreviousForm.next) {
                    $("#next-submission").show().click(function () {
                        window.location = "index.php?option=com_uniform&view=submission&data_id=" + self.nextAndPreviousForm.next + "&layout=detail";
                    });
                } else {
                    $("#next-submission").hide();
                }

                if (this.nextAndPreviousForm.previous) {
                    $("#previous-submission").show().click(function () {
                        window.location = "index.php?option=com_uniform&view=submission&data_id=" + self.nextAndPreviousForm.previous + "&layout=detail";
                    });
                } else {
                    $("#previous-submission").hide();
                }
                $("#jform_form_type").change(function () {
                    if ($(this).val() == 2) {
                        $(".jsn-page-actions").show();
                        $(".jsn-section-content div.submission-page").hide();
                        $($(".jsn-section-content div.submission-page")[0]).show();
                        $(".jsn-section-content hr").remove();
                        $(".jsn-section-content .submission-content .jsn-page-actions button").show();
                        self.checkPage();
                    } else if ($(this).val() == 1) {
                        $(".jsn-page-actions").hide();
                        $(".jsn-section-content div.submission-page").show();
                        $(".jsn-section-content div.submission-page").each(function (i) {
                            if (i != 0) {
                                $(this).before("<hr/>");
                            }
                        })
                    }
                }).change();
                if (!$("#jform_form_type").attr("data-value")) {
                    $(".jsn-page-actions").hide();
                    $(".jsn-section-content div.submission-page").show();
                }
                $($(".jsn-section-content div.submission-page")[0]).show();
                this.checkPage();
            },
            checkPage:function () {
                $(".jsn-section-content div.submission-page").each(function (i) {
                    if (!$(this).is(':hidden')) {
                        if ($(this).next().attr("data-value")) {
                            $(".jsn-page-actions .next-page").removeAttr("disabled");
                        } else {
                            $(".jsn-page-actions .next-page").attr("disabled", "disabled");
                        }
                        if ($(this).prev().attr("data-value")) {
                            $(".jsn-page-actions .prev-page").removeAttr("disabled");
                        } else {
                            $(".jsn-page-actions .prev-page").attr("disabled", "disabled");
                        }
                    }
                });
            },
            nextpaginationPage:function () {
                var self = this;
                $(".jsn-section-content div.submission-page").each(function () {
                    if (!$(this).is(':hidden')) {
                        $(this).hide();
                        $(this).next().show();
                        self.checkPage();
                        return false;
                    }
                });
            },
            prevpaginationPage:function () {
                var self = this;
                $(".jsn-section-content div.submission-page").each(function () {
                    if (!$(this).is(':hidden')) {
                        $(this).hide();
                        $(this).prev().show();
                        self.checkPage();
                        return false;
                    }
                });
            }
        }
        return JSNUniformSubmissionView;
    });