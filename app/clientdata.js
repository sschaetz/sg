/**
 * This is the main data structure of the client appliaction
 */

function Clientdata_friend()
{
  this.url          = "";
  this.username     = "";
  this.alias        = "";
  this.localkey     = "";
  this.remotekey    = "";
  this.sharedsecret = "";
  this.localsecret  = "";
  /* status of friends: -1 = notset, 0 = sent invite, 1 = friends, 
      2 = sent invite but declined, 3 = received invite but declined 
      4 = ignoring */
  this.status       = -1;
}

function Clientdata()
{
  this.url          = unset_value;
  this.writekey     = unset_value;
  this.friends      = Array();
}

sg = function () 
{

	// private member variable
	var clientdata = new Clientdata();
	
	// a private method
	var privatemethod = function () 
	{
	}
	
	return  {
	  // public member variable
		publicproperty: "asdf",
		// public method
		publicmethod: function () 
		{
		}
	};

}();


