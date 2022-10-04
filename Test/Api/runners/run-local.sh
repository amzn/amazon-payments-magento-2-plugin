#!/bin/bash

set -e

# RUN the SPC API suite
rm -rf screens/*
node scripts/setup-checkout-session.js \
&& ../../../vendor/bin/phpunit -c $(pwd)/phpunit_rest.xml
