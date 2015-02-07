function readFile(file, selector, crop, options)
{
    if (crop) {
        var reader = new FileReader();
        reader.onloadend = function () {
            var image = new Image();
            image.src = reader.result;
            image.onload = function () {
                options.trueSize = [this.width, this.height];
                setTimeout(function () {
                    $("#" + selector + "-image").Jcrop(options);
                }, 500);
                $("#" + selector + "-modal").modal("show");
                $("#" + selector + "-image").attr('src', reader.result);
            }
        };
        reader.readAsDataURL(file.files[0]);
    }
    $("#" + selector + "-name").html(file.files[0].name);
}
