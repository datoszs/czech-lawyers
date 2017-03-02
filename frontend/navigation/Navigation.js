import React from 'react';
import {Link} from 'react-router';
import {Navbar, Nav} from 'react-bootstrap';
import RouteNavItem from './RouteNavItem';

import {Msg} from '../containers';

import about from '../about';
import contact from '../contact';

const Navigation = () => (
    <Navbar>
        <Navbar.Header>
            <Navbar.Brand><Link to="/"><Msg msg="app.title" /></Link></Navbar.Brand>
            <Navbar.Toggle />
        </Navbar.Header>
        <Navbar.Collapse>
            <Nav pullRight>
                <RouteNavItem route={about.ROUTE}><Msg msg="about.title" /></RouteNavItem>
                <RouteNavItem route={contact.ROUTE}><Msg msg="contact.title" /></RouteNavItem>
            </Nav>
        </Navbar.Collapse>
    </Navbar>
);

export default Navigation;
