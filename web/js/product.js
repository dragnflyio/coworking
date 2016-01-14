// Preview products
$('.btn-import').click(function(){
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
$('.btn-save').click(function(){
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
