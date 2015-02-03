function destroyJcrop(selector)
{
    var img = $("#" + selector);
    img.data("Jcrop").destroy();
    img.removeAttr("style");
}

function setCoords(selector, c)
{
    $("#" + selector + "-x").val(c.x);
    $("#" + selector + "-w").val(c.w);
    $("#" + selector + "-y").val(c.y);
    $("#" + selector + "-h").val(c.h);
}
