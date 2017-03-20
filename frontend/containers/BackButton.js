import React from 'react';
import {browserHistory} from 'react-router';
import {Button,Glyphicon} from 'react-bootstrap';
import Msg from './Msg';

export default () => (
    <Button onClick={() => browserHistory.goBack()}>
        <Glyphicon glyph="chevron-left" />
        <Msg msg="back.button" />
    </Button>
);

