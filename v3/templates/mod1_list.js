$(document).ready(function() {

    var tableheight   = 500;
    var tableWidth    = $("h2").width();
    var tableToScroll = $("#translationObjectList");

    $("tr:odd", tableToScroll).addClass("odd");

    if ( tableToScroll.height() > tableheight) {
        tableToScroll.Scrollable(tableheight, tableWidth);
    }

    $("a.tooltip").tooltip({
        bodyHandler: function () {
            return $($(this).attr("href")).html();
        },
        showURL: false
    });
});