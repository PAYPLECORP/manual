const app = require('./app')

let port = 3000

app.listen(port, error => console.log(error ? error : `??  Started on port ${ port }`))