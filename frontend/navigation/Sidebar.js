import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {ButtonGroup, Glyphicon} from 'react-bootstrap';
import classNames from 'classnames';
import {Msg} from '../containers';
import router from '../router';
import {CONTACT, CONTACT_FORM_ANCHOR} from '../routes';
import Button from './SidebarButton';
import styles from './Sidebar.less';

class Sidebar extends Component {
    constructor(props) {
        super(props);
        this.state = {
            displayed: true,
            closeDisplayed: false,
        };

        this.hide = this.hide.bind(this);
        this.showClose = this.showClose.bind(this);
        this.hideClose = this.hideClose.bind(this);
    }

    hide() {
        this.setState({displayed: false});
    }
    showClose() {
        this.setState({closeDisplayed: true});
    }
    hideClose() {
        this.setState({closeDisplayed: false});
    }

    render() {
        if (this.state.displayed) {
            return (
                <ButtonGroup onMouseEnter={this.showClose} onMouseLeave={this.hideClose} className={classNames(styles.main, 'hidden-xs')}>
                    <Button onClick={this.props.goToContact} className={styles.button}><Msg msg="contact.appeal" /></Button>
                    {
                        this.state.closeDisplayed &&
                        <Button onClick={this.hide} className={classNames(styles.button, styles.close)}><Glyphicon glyph="remove-sign" /></Button>
                    }
                </ButtonGroup>
            );
        } else {
            return null;
        }
    }
}

Sidebar.propTypes = {
    goToContact: PropTypes.func.isRequired,
};

const mapDispatchToProps = (dispatch) => ({
    goToContact: () => dispatch(router.transition(CONTACT, undefined, undefined, CONTACT_FORM_ANCHOR)),
});

export default connect(undefined, mapDispatchToProps)(Sidebar);
