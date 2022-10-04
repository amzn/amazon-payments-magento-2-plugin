import * as fs from 'fs';
var config = JSON.parse(fs.readFileSync('scripts/config/selenium-config.json'));
var postmanEnvironment = JSON.parse(fs.readFileSync('environments/checkout.postman_env.json'));

if (process.argv[2] === 'remote') {
    if (process.argv[3]) {
        config.base_url = process.argv[3].toString();
    }

    fs.writeFileSync('scripts/config/selenium-config.json', JSON.stringify(config, null, '\t'));
}

postmanEnvironment.values
    .filter(value => value.key === 'base_url')
    .map(value => value.value = config.base_url);
    
postmanEnvironment.values
    .filter(value => value.key === 'graphql_endpoint')
    .map(value => value.value = config.base_url.concat('/graphql'));

fs.writeFileSync('environments/checkout.postman_env.json', JSON.stringify(postmanEnvironment, null, '\t'));
