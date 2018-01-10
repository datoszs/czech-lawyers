import React, {Component} from 'react';
import {Alert} from 'react-bootstrap';
import {Msg} from '../containers';
import styles from './NssErrorAlert.less';

export default class NssErrorAlert extends Component {

    constructor() {
        super();
        this.state = {
            expanded: true,
        };
        this.collapse = this.collapse.bind(this);
    }

    collapse() {
        this.setState({expanded: false});
    }

    render() {
        return this.state.expanded && <Alert bsStyle="danger" className={styles.main} onDismiss={this.collapse}><Msg msg="nss.error" /></Alert>;
    }
}
