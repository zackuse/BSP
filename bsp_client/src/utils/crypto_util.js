import crypto from 'crypto'
class crypto_util {
  static encode (data, password) {
    const cipher = crypto.createCipher('aes192', password)
    // 使用该对象的update方法来指定需要被加密的数据
    let crypted = cipher.update(data, 'utf-8', 'hex')

    crypted += cipher.final('hex')
    return crypted
  }

  static decode (data, password) {
    const decipher = crypto.createDecipher('aes192', password)
    let decrypted = decipher.update(data, 'hex', 'utf-8')
    decrypted += decipher.final('utf-8')

    return decrypted
  }
}

export {
  crypto_util
}
