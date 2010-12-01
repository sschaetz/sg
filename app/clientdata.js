Namespace('starbase.core');
Namespace('starbase.core.communication');
Namespace('starbase.core.secrets');


starbase.core.init = 
function(url, password, loginSalt, masterKeySalt)
{
  starbase.core.url = url;
  starbase.core.secret.password = password;
  starbase.core.secret.masterKeySalt = masterKeySalt;
  starbase.core.secret.loginKey = starbase.hash.sha256(password, loginSalt);
}

starbase.core.communication.loadData =
function()
{
  $.ajax(
  {
    url: starbase.core.url,
    data: 
    {
      loginkey: starbase.core.secret.loginKey
    },
    success: function(answer)
    {
      starbase.core.data = starbase.crypto.aes.decrypt(
        starbase.core.secret.password, 
        starbase.core.secret.masterKeySalt, answer.data);
      console.log('data loaded!');
    }
  });
}

starbase.core.communication.saveData =
function()
{
  $.ajax(
  {
    url: starbase.core.url, type: "POST",
    data: 
    {
      data: starbase.crypto.aes.encrypt(starbase.core.secret.password, 
        starbase.core.secret.masterKeySalt, starbase.core.data),
      loginkey: starbase.core.secret.loginKey
    },
    success: function(answer)
    {
      console.log('data saved!');
    }
  });
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

