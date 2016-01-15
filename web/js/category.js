// Validation category form.
validation['name'] = {minlength:1, message: 'Nhập tên danh mục!!'};
validation['code'] = {minlength:1, message: 'Nhập mã danh mục!'};
// Save category into database.
$(".btn-save").click(function(e){
  e.preventDefault();
  if (beforePost() == true) {
    $.ajax({
      type: 'POST',
      url: '/category/ajax-action',
      data: $('#f_category').serialize()
    })
    .done(function (data) {
      alert('Thêm thành công danh mục');
      $('#f_category').trigger("reset");
    })
    return false;
  }
});
