function destroyJcrop(selector)
{
    var img = $("#" + selector);
    img.data("Jcrop").destroy();
    img.removeAttr("style");
}

function setCoords(c, selector)
{
    $("#" + selector + "-x").val(c.x);
    $("#" + selector + "-x2").val(c.x2);
    $("#" + selector + "-y").val(c.y);
    $("#" + selector + "-y2").val(c.y2);
}

function readFile(file, selector, options, crop)
{
    if (crop) {
        var reader = new FileReader();
        reader.onloadend = function () {
            $("#" + selector + "-modal").modal("show");
            $("#" + selector + "-image").attr('src', reader.result);
            setTimeout(function () {
                $("#" + selector + "-image").Jcrop(options);
            }, 500);
        };
        reader.readAsDataURL(file.files[0]);
    }
    $("#" + selector + "-name").html(file.files[0].name);
}
