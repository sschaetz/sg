/*
$('#x02 ul').click(function (e) {
	if ($(e.target).parent().parent().is('li'))
		$(e.target).parent().parent().toggleClass('highlight');
	return false;
});
http://lab.distilldesign.com/event-delegation/#x02
*/

(function( $, undefined ) {

$.widget("ui.sglist",
{
	options: 
  {
    listname: 'sglist',
    listid: 'sglist-id'
	},

	_create: function(options) 
  {
    $.extend(this.options, options);
    // create the list with event handler
    $("<ul/>", {
      "class": this.options.listname,
      id: this.options.listid,
      click: jQuery.proxy(this._elementClickEvent, this)
    }).appendTo(this.element);
    
    this.element.data('numElements', 0);
	},

  // private methods
  
  _pushTop: function(element, id, classid)
  {
    var content = (typeof element === 'function') ? element() : element;
    var classstring = this.options.listname + '-elem, ' 
      + this.options.listname + '-elem-' + id +
      (classid ? ', ' + classid : '');
    $("<li/>", {
      text: content,
      "class": classstring               
    }).prependTo('.'+this.options.listname).data('id', id);

    this.element.data('numElements', this.element.data('numElements') + 1);
  },

  _remove: function(id)
  {
    $('.'+this.options.listname + '-elem-' + id).detach();
    this.element.data('numElements', this.element.data('numElements') - 1);
  },

  _elementClickEvent: function(e)
  {
	  if($(e.target).is('li'))
		{
      this._trigger('elementclicked', 0, $(e.target).data('id')); 
    }
  },

	destroy: function() 
  {
    this.element.empty();
		return this;
	},

  // public methods
  
  // create a new element and push it to the top
  pushTop: function(element, id, classid) 
  {
    this._pushTop(element, id, classid);
  },
  
  // remove an element by its id
  remove: function(id)
  {
    this._remove(id);
  },

});

$.extend($.ui.sglist);

})(jQuery);

