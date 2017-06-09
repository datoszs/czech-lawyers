import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {ButtonGroup, Glyphicon} from 'react-bootstrap';
import {Msg} from '../containers';
import router from '../router';
import {CONTACT, CONTACT_FORM_ANCHOR} from '../routes';
import Button from './SidebarButton';

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
                <ButtonGroup id="sidebar" onMouseEnter={this.showClose} onMouseLeave={this.hideClose} className="hidden-xs">
                    <Button onClick={this.props.goToContact}><Msg msg="contact.appeal" /></Button>
                    {
                        this.state.closeDisplayed &&
                        <Button className="close-btn" onClick={this.hide}><Glyphicon glyph="remove-sign" /></Button>
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
