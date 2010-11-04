Namespace('starbase.crypto.aes');

starbase.crypto.aes.encrypt = 
function(password, salt, value)
{
  var salt_conv = sjcl.codec.hex.toBits(salt);
  var key = sjcl.misc.pbkdf2(password, salt_conv, 1000, 256);
  return sjcl.json.encrypt(key, JSON.stringify(value));
}

starbase.crypto.aes.decrypt = 
function(password, salt, value)
{
  var salt_conv = sjcl.codec.hex.toBits(salt);
  var key = sjcl.misc.pbkdf2(password, salt_conv, 1000, 256);
  return JSON.parse(sjcl.json.decrypt(key, value));
}


Namespace('starbase.hash');
starbase.hash.sha256 = 
function(value, salt)
{
  return sjcl.codec.hex.fromBits(sjcl.hash.sha256.hash(value + salt));
}

Namespace('starbase.crypto.diffie');

starbase.crypto.diffie.p = 
  str2bigInt("2852573452814171251977686343882970312030571091793156640582"+ 
  "55272453866237368990737225222645155624363254194859821105540997257515" +
  "90544077410610565797547919762048828292881016602804880527015790402138" +
  "66307674733741301553681152809222757055893740935859429763945694282596" +
  "88367109067087051514050260846984364623875389010536790867030029123682" +
  "66026749525873319894437966749682986375750909800733381689336819646806" +
  "04937038201357619421490046071858898537054876320359077808890078084769" +
  "76274995966200639049565127299108889721454975973097720459439192800369" +
  "90594034007762668449207055414216059663934999402662062564772563777043" +
  "356879672749359", 10, 2048);
  
starbase.crypto.diffie.g = str2bigInt("2", 10, 80);


starbase.crypto.diffie.publickey =
function(secret)
{
  var a = str2bigInt(secret, 16, 2048);
  return bigInt2str(powMod(this.g, a, this.p), 16);
}

starbase.crypto.diffie.secretkey =
function(secret, pub)
{
  var a = str2bigInt(secret, 16, 2048);
  var B = str2bigInt(pub, 16, 2048);
  return bigInt2str(powMod(B, a, this.p), 16);
}

