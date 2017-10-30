import React, {Component} from 'react';
import {Modal, Button} from 'react-bootstrap';
import {parse} from 'query-string';
import {Msg, RichText} from '../containers';

export default class Redirect extends Component {
    constructor() {
        super();
        this.state = {
            displayed: !!parse(document.location.search).old,
        };
        this.hide = this.hide.bind(this);
    }

    hide() {
        this.setState({displayed: false});
    }

    render() {
        return (
            <Modal show={this.state.displayed} onHide={this.hide} bsSize="large">
                <Modal.Header>
                    <Modal.Title><Msg msg="redirect.title" /></Modal.Title>
                </Modal.Header>
                <Modal.Body><RichText msg="redirect.text" /></Modal.Body>
                <Modal.Footer>
                    <Button bsStyle="primary" onClick={this.hide}><Msg msg="common.ok" /></Button>
                </Modal.Footer>
            </Modal>
        );
    }
}
