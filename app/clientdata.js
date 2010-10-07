/**
 * This is the main data structure of the client appliaction
 */

function inviteFriend(url, username, alias, localkey, localsecret)
{
  this.url = url;
  this.username = username;
  this.alias = alias;
  this.localkey = localkey;
  this.localsecret = localsecret;
  this.status = 0;
}

function Clientdata_friend()
{
  this.url          = unset_value;
  this.username     = unset_value;
  this.alias        = unset_value;
  this.localkey     = unset_value;
  this.remotekey    = unset_value;
  this.sharedsecret = unset_value;
  this.localsecret  = unset_value;
  /* status of friends: -1 = notset, 0 = sent invite, 1 = friends, 
      2 = sent invite but declined, 3 = received invite but declined */
  this.status       = -1;
}


function Clientdata()
{
  this.url          = unset_value;
  this.writekey     = unset_value;
  this.friends      = Array();
  this.inviteFriend = inviteFriend;
}



