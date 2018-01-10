import React, {Component} from 'react';
import {Alert} from 'react-bootstrap';
import {Msg} from '../containers';
import styles from './NssErrorAlert.less';

const KEY = 'nss.error.collapsed';

export default class NssErrorAlert extends Component {
    constructor() {
        super();
        this.state = {
            expanded: !window.localStorage.getItem(KEY),
        };
        this.collapse = this.collapse.bind(this);
    }

    collapse() {
        window.localStorage.setItem(KEY, true);
        this.setState({expanded: false});
    }

    render() {
        return this.state.expanded && <Alert bsStyle="danger" className={styles.main} onDismiss={this.collapse}><Msg msg="nss.error" /></Alert>;
    }
}
