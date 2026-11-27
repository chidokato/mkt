setTimeout(function() {
    $('#hidden').fadeOut('fast');
}, 5000); // <-- time in milliseconds

$(document).ready(function(){
    $('#changepassword').change(function(){
        if ($(this).is(":checked")) {
            $(".pass").removeAttr('disabled');
        }else{
            $(".pass").attr('disabled','');
        }
    });
});

// change category lang
$(document).ready(function(){
    $("#parent").change(function(){
        var id = $(this).val();
        // alert(id);
        $.get("ajax/change_cate_lang/"+id,function(data){
            $("#list_parent").html(data);
        });
    });
});
// change category lang

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

// change tỉnh thành quận huyện
$(document).ready(function(){
    $("select#province").change(function(){
        var id = $(this).val();
        $.get("ajax/change_province/"+id,function(data){
            $("#district").html(data);
            $("#ward").html('<option value="">...</option>');
            $("#street").html('<option value="">...</option>');
        });

    });
});
$(document).ready(function(){
    $("select#district").change(function(){
        var id = $(this).val();
        $.get("ajax/change_district/"+id,function(data){
            $("#ward").html(data);
        });
        $.get("ajax/change_district_street/"+id,function(data){
            $("#street").html(data);
        });
    });
});


// change sort by category
$(document).ready(function(){
    $("select#sort_by").change(function(){
        var sort_by = $(this).val();
        $.get("ajax/change_SortBy/"+sort_by,function(data){
            $("#parent").html(data);
        });
    });

    $("select#parent").change(function(){
        var id = $(this).val();
        // alert(id);
        $.get("ajax/change_parent/"+id,function(data){
            $("#load_category").html(data);
        });
    });

    $("input#view").blur(function(){
        var view = $(this).val();
        var id = $(this).parents('#category').find('input[name="id"]').val();
        // alert(id);
        $.get("ajax/update_category_view/"+id+"/"+view,function(data){
            // $("#load_category").html(data);
        });
    });

    $("input#menu_view").blur(function(){
        var view = $(this).val();
        var id = $(this).parents('#menu').find('input[name="id"]').val();
        // alert(id);
        $.get("ajax/update_menu_view/"+id+"/"+view,function(data){
            // $("#load_category").html(data);
        });
    });
});

// sản phẩm
$(document).ready(function(){
    $("button#del_img_detail").click(function(){
        var id = $(this).parents('#detail_img').find('input[id="id_img_detail"]').val();
        // alert(id);
        $.ajax({
            url: 'ajax/del_img_detail/'+id,
            type: 'GET',
            cache: false,
            data: {
                "id":id
            },
        });
    });
}); // xóa ảnh trong db
$(document).ready(function(){
    $("input#name_img_detail").blur(function(){
        var name = $(this).val();
        var id = $(this).parents('#detail_img').find('input[id="id_img_detail"]').val();
        $.ajax({
            url: 'ajax/name_img_detail/'+id+"/"+name,
            type: 'GET',
            cache: false,
            data: {
                "id":id
            },
        });
    });
});
$(document).ready(function(){
    $("button#del_section").click(function(){
        var id = $(this).parents('#section').find('input[id="id_section"]').val();
        // alert(id);
        $.ajax({
            url: 'ajax/del_section/'+id,
            type: 'GET',
            cache: false,
            data: {
                "id":id
            },
        });
    });
}); // xóa ảnh trong db


$(document).ready(function(){
    $("input#status").click(function(){
        var status = $(this).is(':checked');
        var id = $(this).parents('#category').find('input[id="id"]').val();
        // alert(id);
        $.ajax({
            url:  'ajax/update_status_category/'+id+'/'+status, type: 'GET', cache: false, data: {
            },
        });
    });
}); // update status

$(document).ready(function(){
    $(document).on('click', 'input#status_post', function(){
        var status = $(this).is(':checked');
        var id = $(this).closest('tr[data-id]').data('id');
        if (!id) {
            return;
        }
        $.ajax({
            url:  'ajax/update_status_post/'+id+'/'+status, type: 'GET', cache: false, data: {
            },
        });
    });
}); // update status

$(document).ready(function(){
    $("input#status_province").click(function(){
        var status = $(this).is(':checked');
        var id = $(this).parents('#province').find('input[id="id"]').val();
        // alert(id);
        $.ajax({
            url:  'ajax/update_status_province/'+id+'/'+status, type: 'GET', cache: false, data: {
            },
        });
    });
}); // update status

$(document).ready(function(){
    $("input#home_province").click(function(){
        var status = $(this).is(':checked');
        var id = $(this).parents('#province').find('input[id="id"]').val();
        // alert(id);
        $.ajax({
            url:  'ajax/update_home_province/'+id+'/'+status, type: 'GET', cache: false, data: {
            },
        });
    });
}); // update status

$(document).ready(function(){
    $(document).on('click', 'input#hot_post', function(){
        var hot = $(this).is(':checked');
        var id = $(this).closest('tr[data-id]').data('id');
        if (!id) {
            return;
        }
        $.ajax({
            url:  'ajax/update_hot_post/'+id+'/'+hot, type: 'GET', cache: false, data: {
            },
        });
    });
}); // update hot


$("select#category").change(function(){
    var id = $(this).val();
    // alert(id);
    $.get("ajax/change_category/"+id,function(data){
        $("#loadcustom").html(data);
    });
});


// imagePreview khi thêm nhiều ảnh
var selectedFiles = [];
document.getElementById('customFileInput').addEventListener('click', function() {
    console.log('Custom file input clicked');
    document.getElementById('imgInput').click();
});
document.getElementById('imgInput').addEventListener('change', function(event) {
    var files = Array.from(event.target.files);
    selectedFiles = selectedFiles.concat(files);
    updateImagePreview();
});
function updateImagePreview() {
    var preview = document.getElementById('imagePreview');
    preview.innerHTML = ''; // Clear any existing previews

    selectedFiles.forEach(function(file, index) {
        var reader = new FileReader();

        reader.onload = function(e) {
            var imgContainer = document.createElement('span');
            imgContainer.className = 'image-container';

            var img = document.createElement('img');
            img.src = e.target.result;

            var deleteButton = document.createElement('button');
            deleteButton.className = 'delete-button';
            deleteButton.innerText = 'X';
            deleteButton.addEventListener('click', function() {
                selectedFiles.splice(index, 1);
                updateImagePreview();
            });

            imgContainer.appendChild(img);
            imgContainer.appendChild(deleteButton);
            preview.appendChild(imgContainer);
        }

        reader.readAsDataURL(file);
    });
    var dataTransfer = new DataTransfer();
    selectedFiles.forEach(function(file) {
        dataTransfer.items.add(file);
    });
    document.getElementById('imgInput').files = dataTransfer.files;
}

$(document).ready(function() {
    let sectionCount = -1;
    $('#addSectionButton').click(function() {
        sectionCount++;
        $.get('admin/get-section', function(data) {
            let newSection = $(data);
            newSection.attr('id', 'section-' + sectionCount);  // Set unique ID
            newSection.find('input[name="img_ss[]"]').attr('name', 'img_ss' + sectionCount + '[]');  // Update input file name
            $('#sectionContainer').append(newSection);
            newSection.find('.editor').each(function() {
                ClassicEditor
                    .create(this, {
                        toolbar: [
                            'undo', 'redo', 'imageUpload', 
                            'bold', 'italic', 'heading', 'bulletedList', 'numberedList', 
                            'link', 'insertTable', 'blockQuote', 'removeFormat'
                        ],
                        ckfinder: {
                            uploadUrl: '{{ route("uplsssoada") }}?command=QuickUpload&type=Files&_token={{ csrf_token() }}',
                            options: {
                                resourceType: 'Images'
                            }
                        }
                    })
                    .then(editor => {
                        console.log('Editor was initialized', editor);
                    })
                    .catch(error => {
                        console.error(error);
                    });
            });
        });
    });

    $('#sectionContainer').on('click', '.remove-section', function() {
        $(this).closest('.section').remove();
    });
});


document.addEventListener('DOMContentLoaded', function() {
    var links = document.querySelectorAll('.scroll-link');

    links.forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            var targetId = this.getAttribute('href').substring(1);
            var targetElement = document.getElementById(targetId);

            if (targetElement) {
                smoothScrollTo(targetElement, 300, this);
            }
        });
    });

    window.addEventListener('scroll', function() {
        var currentPosition = window.scrollY;

        links.forEach(function(link) {
            var targetId = link.getAttribute('href').substring(1);
            var targetElement = document.getElementById(targetId);

            if (targetElement) {
                var targetPosition = targetElement.offsetTop;
                var targetHeight = targetElement.offsetHeight;

                if (currentPosition >= targetPosition && currentPosition < targetPosition + targetHeight) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            }
        });
    });
});

function smoothScrollTo(target, duration, link) {
    var targetPosition = target.getBoundingClientRect().top + window.scrollY;
    var startPosition = window.scrollY;
    var distance = targetPosition - startPosition;
    var startTime = null;

    function animation(currentTime) {
        if (startTime === null) startTime = currentTime;
        var timeElapsed = currentTime - startTime;
        var run = ease(timeElapsed, startPosition, distance, duration);
        window.scrollTo(0, run);
        if (timeElapsed < duration) {
            requestAnimationFrame(animation);
        } else {
            window.scrollTo(0, targetPosition);
            link.classList.add('active'); // Thêm lớp 'active' vào liên kết sau khi cuộn hoàn tất
        }
    }

    function ease(t, b, c, d) {
        t /= d / 2;
        if (t < 1) return c / 2 * t * t + b;
        t--;
        return -c / 2 * (t * (t - 2) - 1) + b;
    }

    requestAnimationFrame(animation);
}



