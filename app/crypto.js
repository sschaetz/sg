
function calculate_encryption_key(password, salt)
{
  var salt_conv = sjcl.codec.hex.toBits(salt);
  return sjcl.misc.pbkdf2(password, salt_conv, 1000, 256);
}

function encrypt_block(key, block)
{
  return sjcl.json.encrypt(key, Object.toJSON(block));
}

function decrypt_block(key, block)
{
  return sjcl.json.decrypt(key, block).evalJSON(true);
}

function calculate_sha256(input)
{
  return sjcl.codec.hex.fromBits(sjcl.hash.sha256.hash(input));
}

