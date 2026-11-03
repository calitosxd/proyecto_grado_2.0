function prueba(){
	 document.write('Prueba de JS');
}

function encriptar(){
	var plaintext = "Palabra"; 
    var encrypted = CryptoJS.AES.encrypt(plaintext, "Secret Passphrase"); 
    alert("El texto a encriptar es: " + plaintext + " y encriptado es: " + encrypted.toString()); 

}

function desencriptar(){
    var decrypted = CryptoJS.AES.decrypt(encrypted, "Secret Passphrase"); 
    var encrptedText2 = decrypted.toString(CryptoJS.enc.Utf8); 
    // var decrypted = CryptoJS.AES.decrypt(data, key).toString(CryptoJS.enc.Utf8); 
    alert("El texto encriptado es: " + encrypted.toString + " y desencriptado es: " + encrptedText2); 
}