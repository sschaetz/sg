(function( $, undefined ) {

$.widget("ui.sglist",
{
	options: 
  {
    listname: 'sglist'
	},
	_create: function() 
  {
    // create a list
    this.element.append('<ul class="' + this.options.listname + '-list"><ul>');
	},

	destroy: function() 
  {
    this.element.empty();
		return this;
	}


});

$.extend($.ui.sglist);

})(jQuery);

