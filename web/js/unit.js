var $collectionHolder;
// setup an "add a tag" link
var $addTagLink = $('<button class="btn btn-primary add_tag_link">Add unit</button>');
var $newLinkLi = $('<div></div>').append($addTagLink);

function addTagForm($collectionHolder, $newLinkLi) {
  // Get the data-prototype explained earlier
  var prototype = $collectionHolder.data('prototype');
  console.log(prototype);
  // get the new index
  var index = $collectionHolder.data('index');

  // Replace '__name__' in the prototype's HTML to
  // instead be a number based on how many items we have
  var newForm = prototype.replace(/__name__/g, index);

  // increase the index with one for the next item
  $collectionHolder.data('index', index + 1);

  // Display the form in the page in an li, before the "Add a tag" link li
  var $newFormLi = $('<tr></tr>').append(newForm);
  $newLinkLi.before($newFormLi);
}

function addTagFormDeleteLink($tagFormLi) {
    var $removeFormA = $('<a href="#" class = "glyphicon glyphicon-remove"></a>');
    $tagFormLi.append($removeFormA);

    $removeFormA.on('click', function(e) {
      // prevent the link from creating a "#" on the URL
      e.preventDefault();

      // remove the li for the tag form
      $tagFormLi.remove();
    });
}

jQuery(document).ready(function() {
  // Get the ul that holds the collection of tags
  $collectionHolder = $('tbody.units');

  // add the "add a tag" anchor and li to the tags ul
  $collectionHolder.append($newLinkLi);

  // count the current form inputs we have (e.g. 2), use that as the new
  // index when inserting a new item (e.g. 2)
  $collectionHolder.data('index', $collectionHolder.find(':input').length);

  $addTagLink.on('click', function(e) {
    // prevent the link from creating a "#" on the URL
    e.preventDefault();

    // add a new tag form (see next code block)
    addTagForm($collectionHolder, $newLinkLi);
  });

  $collectionHolder.find('tr').each(function() {
    addTagFormDeleteLink($(this));
  });
});
