function destroyJcrop(selector)
{
    var img = $("#" + selector);
    img.data("Jcrop").destroy();
    img.removeAttr("style");
}

function setCoords(selector, c)
{
    $("#" + selector + "-coords-x").val(c.x);
    $("#" + selector + "-coords-w").val(c.w);
    $("#" + selector + "-coords-y").val(c.y);
    $("#" + selector + "-coords-h").val(c.h);
}
