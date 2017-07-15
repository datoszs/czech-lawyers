import React from 'react';
import {Jumbotron, Alert, Button, Glyphicon} from 'react-bootstrap';
import {Msg} from '../containers';

export default () => (
    <section>
        <Jumbotron>
            <h1><Msg msg="app.title" /></h1>
            <p><Msg msg="home.above" /></p>
            <Alert bsStyle="warning">
                <p>
                    <Msg msg="unavailable.warning" />
                    <Button bsStyle="link" bsSize="large" onClick={() => window.location.reload()}>
                        <Msg msg="common.refresh" /> <Glyphicon glyph="refresh" />
                    </Button>
                </p>
            </Alert>
        </Jumbotron>
    </section>
);
