// Preview products
$('.btn-preview').click(function(){
  $.ajax({
    type: 'POST',
    url: '/product/import-preview',
    dataType: 'json',
    async: false
  })
  .done(function (data) {
    $('.content-preview').html(data);
  })
  return false;
});

// Save products from import
$('.import-action .btn-import').click(function(){
  $.ajax({
    type: 'POST',
    url: '/product/import-save',
    dataType: 'json',
    async: false
  })
  .done(function (data) {
    alert('Bạn đã nhập dữ liệu thành công');
  })
  return false;
});

// Validation product form.
validation['code'] = {minlength:1, message: 'Nhập mã sản phẩm!'};
validation['name_vi'] = {minlength:1, message: 'Nhập tên sản phẩm!'};
validation['name_en'] = {minlength:1, message: 'Nhập tên sản phẩm!'};
validation['unit'] = {jsfunction: function(){
  if ($('#unit').val() == -1) return "Chọn đơn vị cho sản phẩm!";
  return '';
}};
validation['category'] = {jsfunction: function(){
  if ($('#category').val() == -1) return "Chọn danh mục cho sản phẩm!";
  return '';
}};
validation['type'] = {jsfunction:function(){
  if($('#type').val() == -1) return "Chọn loại sản phẩm!";
  return '';
}};
validation['price'] = {minvalue:1, message: 'Nhập giá sản phẩm, lớn hơn 0 (zero)'};
/*validation['code'] = {jsfunction:function(){
  var code = $('#code').val();
  var status = 1;
  $.ajax({
    type: 'POST',
    url: '/product/validate-code',
    data: {'code': code,}
  })
  .success(function(data){
    status = data['status'];
  });
  if (status == 0) {
    return 'Mã sản phẩm này đã tồn tại!';
  }
  else {
    return '';
  }
}};*/

// Add/Update product.
$('.btn-submit').click(function(e){
  e.preventDefault();
  if (beforePost() == true) {
    $.ajax({
      type: 'POST',
      url: '/product/ajax-action',
      data: $('#f_product').serialize()
    })
    .done(function (data) {
      alert(data['message']);
      $('#f_product').trigger("reset");
    })
    return false;
  }
});
