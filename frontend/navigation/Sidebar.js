import React, {Component} from 'react';
import {ButtonGroup, Glyphicon} from 'react-bootstrap';
import {Msg} from '../containers';
import {transition} from '../util';
import contact from '../contact';
import Button from './SidebarButton';

const goToContact = () => transition(contact);

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
                    <Button onClick={goToContact}><Msg msg="contact.appeal" /></Button>
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

export default Sidebar;
