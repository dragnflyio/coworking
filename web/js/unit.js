var $collectionHolder;
// setup an "add a tag" link
var $addTagLink = $('<button class="btn btn-primary add_tag_link">Add unit</button>');
var $newLinkLi = $('<div></div>').append($addTagLink);

function addTagForm($collectionHolder, $newLinkLi) {
  var prototype = $collectionHolder.data('prototype');
  console.log(prototype);
  var index = $collectionHolder.data('index');
  var newForm = prototype.replace(/__name__/g, index);
  $collectionHolder.data('index', index + 1);
  var $newFormLi = $('<tr></tr>').append(newForm);
  $newLinkLi.before($newFormLi);
}

function addTagFormDeleteLink($tagFormLi) {
  var $removeFormA = $('<a href="#" class = "glyphicon glyphicon-remove"></a>');
  $tagFormLi.append($removeFormA);
  $removeFormA.on('click', function(e) {
    // prevent the link from creating a "#" on the URL
    e.preventDefault();

    if (confirm('Bạn chắc chắn muốn xóa đơn vị này?')) {
      $tagFormLi.remove();
    }
  });
}

jQuery(document).ready(function() {
  $collectionHolder = $('tbody.units');
  $collectionHolder.append($newLinkLi);
  $collectionHolder.data('index', $collectionHolder.find(':input').length);

  $addTagLink.on('click', function(e) {
    e.preventDefault();
    addTagForm($collectionHolder, $newLinkLi);
  });

  $collectionHolder.find('tr').each(function() {
    addTagFormDeleteLink($(this));
  });
});
