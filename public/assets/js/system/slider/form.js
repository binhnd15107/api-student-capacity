const elForm = "#formAddSlider";
const onkeyup = false;
const rules = {
    link_to: {
        required: true,
    },
    start_time: {
        required: true,
    },
    end_time: {
        required: true,
    },
};
const messages = {
    link_to: {
        required: "Chưa nhập trường này !",
    },

    start_time: {
        required: "Chưa nhập trường này !",
        min: "Thời gian bắt đầu không được nhỏ hơn thời gian hiện tại !",
        max: "Vui  lòng nhập thời gian bắt đầu nhỏ hơn thời gian kết thúc !",
    },
    end_time: {
        required: "Chưa nhập trường này !",
        min: "Thời gian kết thúc không được lớn hơn thời gian bắt đầu !",
    },
};

const pageSliderForm = {
    tabSelect: function () {
        $(".btn-major").on("click", function (e) {
            $("#major").show();
            $("#round").hide();
            $(".form-round").val(0);
            $(this).addClass("btn-primary");
            $(".btn-round").removeClass("btn-primary");
            $(".btn-home").removeClass("btn-primary");
            $(".btn-home-re").removeClass("btn-primary");
        });
        $(".btn-round").on("click", function (e) {
            $("#round").show();
            $("#major").hide();
            $(".form-major").val(0);
            $(".btn-major").removeClass("btn-primary");
            $(".btn-home-re").removeClass("btn-primary");
            $(".btn-home").removeClass("btn-primary");
            $(this).addClass("btn-primary");
        });
        $(".btn-home").on("click", function (e) {
            $(this).addClass("btn-primary");

            $(".btn-round").removeClass("btn-primary");
            $(".btn-major").removeClass("btn-primary");
            $("#major").hide();
            $(".form-round").val(0);
            $("#round").hide();
            $(".form-major").val(0);
        });
    },
    showFile: function () {
        $(".file-change").on("change", function () {
            var file = $(this)[0].files[0];
            if (!file) return;
            let render = new FileReader();
            render.onload = function () {
                $("#previewImg").attr("src", this.result);
            };
            render.readAsDataURL(file);
        });
    },
};
pageSliderForm.tabSelect();
pageSliderForm.showFile();
