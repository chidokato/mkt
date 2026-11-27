setTimeout(function() {
    $('#hidden').fadeOut('fast');
}, 5000); // <-- time in milliseconds

$(document).ready(function(){
  $("#checkbox").click(function(){
    $(".delall").toggle();
  });
});

$(document).ready(function(){
    $('#changepassword').change(function(){
        if ($(this).is(":checked")) {
            $(".pass").removeAttr('disabled');
        }else{
            $(".pass").attr('disabled','');
        }
    });
});

function dell() {alert("Bạn có chắc muốn xóa bản ghi!");}

function goBack() { window.history.back(); } // back history

// upload images
function readURL(input) {
    if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      $('.image-upload-wrap').hide();
      $('.file-upload-image').attr('src', e.target.result);
      $('.file-upload-content').show();
      $('.image-title').html(input.files[0].name);
    };
    reader.readAsDataURL(input.files[0]);
        } else {
    removeUpload();
    }
}
// upload images

// submit form
// $(document).ready(function() {
//     $('form').submit(function(event) {
//         $.ajax({
//             method: $(this).attr('method'),
//             url: $(this).attr('action'),
//             data: $(this).serialize(),
//             success: function(datas){
//                 // $('#alerts').html(datas);
//             }
//         }).done(function(response) {
//             // Process the response here
//         });
//         event.preventDefault(); // <- avoid reloading
//    });
// });
// submit form

// checkbox all
function toggle(source) {
  checkboxes = document.getElementsByName('foo[]');
  for(var i=0, n=checkboxes.length;i<n;i++) {
    checkboxes[i].checked = source.checked;
  }
}

// category
$(document).ready(function(){
    $("#sort_by").change(function(){
        var sort_by = $(this).val();
        $.get("admin/ajax/sort_by/"+sort_by,function(data){
            $("#parent").html(data);
        });
    });
});

$(document).ready(function(){
    $("input#view").blur(function(){
        var view = $(this).val();
        var cid = $(this).parents('#menu').find('input[name="id"]').val();
        $.ajax({
            url:  'admin/ajax/updateview/'+cid,
            type: 'GET',
            cache: false,
            data: {
                "view":view,
                "cid":cid
            },
            // success: function(data){
            //     $('#data-cat').html(data);
            // }
        });
    });
});

$(document).ready(function(){
    $("input#status_menu").click(function(){
        var status_menu = $(this).is(':checked');
        var id = $(this).parents('#menu').find('input[id="id"]').val();
        // alert(status_menu);
        $.ajax({
            url:  'admin/ajax/update_status_menu/'+id, type: 'GET', cache: false, data: {
                "status_menu":status_menu,
                "id":id
            },
        });
    });
}); // update menu

$(document).ready(function(){
    $("input#status_cat").click(function(){
        var status_cat = $(this).is(':checked');
        var id = $(this).parents('#category').find('input[id="id"]').val();
        // alert(status);
        $.ajax({
            url:  'admin/ajax/update_status_category/'+id, type: 'GET', cache: false, data: {
                "status_cat":status_cat,
                "id":id
            },
        });
    });
}); // update status

$(document).ready(function(){
    $("input#home").click(function(){
        var home = $(this).is(':checked');
        var id = $(this).parents('#category').find('input[id="id"]').val();
        // alert(status);
        $.ajax({
            url:  'admin/ajax/update_home_category/'+id, type: 'GET', cache: false, data: {
                "home":home,
                "id":id
            },
        });
    });
}); // update home

// end category

// news
$(document).ready(function() {
    $('form.editform').submit(function(event) {
        $.ajax({
            method: $(this).attr('method'),
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function(datas){
                // $('#loadchat').html(datas);
                alert('Thành công');
            }
        }).done(function(response) {});
        
        event.preventDefault(); // <- avoid reloading
   });
});
// end news

// nhập hang
$(document).ready(function(){
    $("#sanpham").change(function(){
        var sp_id = $(this).val();
        $.get("admin/ajax/change_sp/"+sp_id,function(data){
            $("#form").html(data);
        });
    });
});
$(document).ready(function(){
    $("#mausac").change(function(){
        var id = $(this).val();
        var a_id = document.getElementById("articles").value;
        // alert(a_id);
        $.get("admin/ajax/mausac1/"+id+"/"+a_id,function(data){
            $("#form").html(data);
        });
    });
});
// nhập hang

// bán hang
$(document).ready(function(){
    $("#articles").change(function(){
        var id = $(this).val();
        $.get("admin/ajax/articles/"+id,function(data){
            $("#mausac").html(data);
        });
    });
}); // change sản phẩm show màu sắc
$(document).ready(function(){
    $("#mausac").change(function(){
        var id = $(this).val();
        var a_id = document.getElementById("articles").value;
        // alert(a_id);
        $.get("admin/ajax/mausac/"+id+"/"+a_id,function(data){
            $("#size").html(data);
        });
    });
});
$(document).ready(function(){
    $("#customer").change(function(){
        var kh_id = $(this).val();
        $.get("admin/ajax/change_khang/"+kh_id,function(data){
            $("#showkhang").html(data);
        });
    });
}); // khách hàng
$(document).ready(function(){
    $("#order").change(function(){
        var od_id = $(this).val();
        $.get("admin/ajax/change_order/"+od_id,function(data){
            $("#showorder").html(data);
        });
    });
}); // đơn hàng
// bán hang

// sửa tồn kho
$(document).ready(function(){
    $("input#status").click(function(){
        var status = $(this).is(':checked');
        var id = $(this).parents('#t_kho').find('input[id="tk_id"]').val();
        // alert(id);
        $.ajax({
            url:  'admin/ajax/updatestatustonkho/'+id,
            type: 'GET',
            cache: false,
            data: {
                "status":status,
                "id":id
            },
        });
    });
});

$(document).ready(function(){
    $("input.edit_tonkho").blur(function(){
        var id = $(this).parents('#t_kho').find('input[id="tk_id"]').val();
        var number = $(this).parents('#t_kho').find('input[id="number"]').val();
        var sizechan = $(this).parents('#t_kho').find('input[id="sizechan"]').val();
        var price = $(this).parents('#t_kho').find('input[id="price"]').val();
        var f_id = $(this).parents('#t_kho').find('input[id="f_id"]').val();
        var sp_id = $(this).parents('#t_kho').find('input[id="sp_id"]').val();
        // alert(sizechan);
        $.ajax({
            url:  'admin/ajax/updatetonkho/'+id,
            type: 'GET',
            cache: false,
            data: {
                "id":id,
                "number":number,
                "sizechan":sizechan,
                "price":price,
                "f_id":f_id,
                "sp_id":sp_id
            },
        });
    });
});
// sửa tồn kho

// sản phẩm
$(document).ready(function(){
    $("button#del_img_detail").click(function(){
        var id = $(this).parents('#detail_img').find('input[id="id_img_detail"]').val();
        // alert(id);
        $.ajax({
            url: 'admin/ajax/del_img_detail/'+id,
            type: 'GET',
            cache: false,
            data: {
                "id":id
            },
        });
    });
}); // xóa ảnh trong db

$(document).on('click', '#del_img_detail', function(e){ 
    e.preventDefault();
    $(this).parent('#detail_img').remove();
    x--;
}); // xóa thẻ div chứ ảnh

$(document).ready(function(){
    $("input#status_articles").click(function(){
        var status_articles = $(this).is(':checked');
        var id = $(this).parents('#articles').find('input[id="id"]').val();
        // alert(status);
        $.ajax({
            url:  'admin/ajax/update_status_articles/'+id, type: 'GET', cache: false, data: {
                "status_articles":status_articles,
                "id":id
            },
        });
    });
}); // update status 

// section
$(document).ready(function() {
    $('form#add_section').submit(function(event) {
        $.ajax({
            method: $(this).attr('method'),
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function(datas){
                $('#load_html_section').html(datas);
                // location.reload();
            }
        }).done(function(response) {
            // alert('thành công');
        });
        event.preventDefault(); // <- avoid reloading
    });
    $('button#del_section').on('click', function(){
        var id = $(this).parents('#section_list').find('input[id="section_id"]').val();
        // alert(section_id);
        $.ajax({
            url:  'admin/ajax/del_section/'+id, type: 'GET', cache: false, data: {"id":id},
        });
        $(this).closest("#section_list").remove();
    });
}); // lưu section trong thêm sản phẩm

// section
// end sản phẩm


// themes
$(document).ready(function(){
    $("input#status_themes").click(function(){
        var status = $(this).is(':checked');
        var id = $(this).parents('#themes').find('input[id="id"]').val();
        // alert(status);
        $.ajax({
            url:  'admin/ajax/update_status_themes/'+id, type: 'GET', cache: false, data: {
                "status":status,
                "id":id
            },
        });
    });
}); // update status
// end themes


// location
$(document).ready(function(){
    $("input#status_province").click(function(){
        var status = $(this).is(':checked');
        var id = $(this).parents('#province').find('input[id="id"]').val();
        // alert(status);
        $.ajax({
            url:  'admin/ajax/update_status_province/'+id, type: 'GET', cache: false, data: {
                "status":status,
                "id":id
            },
        });
    });
}); // update status
$(document).ready(function(){
    $("input#status_district").click(function(){
        var status = $(this).is(':checked');
        var id = $(this).parents('#district').find('input[id="id"]').val();
        // alert(status);
        $.ajax({
            url:  'admin/ajax/update_status_district/'+id, type: 'GET', cache: false, data: {
                "status":status,
                "id":id
            },
        });
    });
}); // update status
$(document).ready(function(){
    $("#province").change(function(){
        var id = $(this).val();
        $.get("admin/ajax/change_province/"+id,function(data){
            $("#district").html(data);
        });
    });
});
$(document).ready(function(){
    $("#district").change(function(){
        var id = $(this).val();
        $.get("admin/ajax/change_district/"+id,function(data){
            $("#ward").html(data);
        });
        $.get("admin/ajax/change_district_street/"+id,function(data){
            $("#street").html(data);
        });
    });
});

// location


// seo
function change (el) {
    var max_desc = 170;
    if (el.value.length > max_desc) {
        el.value = el.value.substr(0, max_desc);
    }
    document.getElementById('chars_left').innerHTML = max_desc - el.value.length;
    return true;
}
function changetitle (el) {
    var max_title = 70;
    if (el.value.length > max_title) {
        el.value = el.value.substr(0, max_title);
    }
    document.getElementById('chars_title').innerHTML = max_title - el.value.length;
    return true;
}

$(function(){
    $("input#title").keyup(function () {
        var value = $(this).val();
        $(".seo-title").text(value);
    }).keyup();
});

$(function(){
    $("input#description").keyup(function () {
        var value = $(this).val();
        $(".seo-description").text(value);
    }).keyup();
});

$(function(){
    $("input.price").keyup(function () {
        var value = $(this).val();
        $(".gia").text(value);
    }).keyup();
});
$(function(){
    $("input.oldprice").keyup(function () {
        var value = $(this).val();
        $(".gia_niem_yet").text(value);
    }).keyup();
});
// end seo


// price
$("input[data-type='currency']").on({
    keyup: function() {
      formatCurrency($(this));
    },
    blur: function() { 
      formatCurrency($(this), "blur");
    }
});
function formatNumber(n) {
  return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
}
function formatCurrency(input, blur) {
  var input_val = input.val();
  if (input_val === "") { return; }
  var original_len = input_val.length;
  var caret_pos = input.prop("selectionStart");
  if (input_val.indexOf(".") >= 0) {
    var decimal_pos = input_val.indexOf(".");
    var left_side = input_val.substring(0, decimal_pos);
    var right_side = input_val.substring(decimal_pos);
    left_side = formatNumber(left_side);
    right_side = formatNumber(right_side);
    if (blur === "blur") {
      right_side += "";
    }
    right_side = right_side.substring(0, 2);
    input_val = "" + left_side + "." + right_side;
  } else {
    input_val = formatNumber(input_val);
    input_val = "" + input_val + "";
    if (blur === "blur") {
      input_val += "";
    }
  }
  input.val(input_val);
  var updated_len = input_val.length;
  caret_pos = updated_len - original_len + caret_pos;
  input[0].setSelectionRange(caret_pos, caret_pos);
}

// end news

