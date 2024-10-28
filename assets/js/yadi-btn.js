window.YaAuthSuggest.init(
    {
      client_id: "a34d04e762fa4ab7a07613cba70fdb8c",
      response_type: "token",
      redirect_uri: "http://yadi/"
    },
    "http://yadi/",
    { view: "default" }
  )
  .then(({handler}) => handler())
  .then(data => console.log('Сообщение с токеном', data))
  .catch(error => console.log('Обработка ошибки', error))