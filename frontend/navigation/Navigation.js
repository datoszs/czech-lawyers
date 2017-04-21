import React from 'react';
import {Link} from 'react-router';
import {Navbar, Nav} from 'react-bootstrap';
import RouteNavItem from './RouteNavItem';

import {Msg} from '../containers';

import about from '../about';
import contact from '../contact';
import search from '../search';

const Navigation = () => (
    <Navbar>
        <Navbar.Header>
            <Navbar.Brand><Link to="/"><Msg msg="app.title" /></Link></Navbar.Brand>
            <Navbar.Toggle />
        </Navbar.Header>
        <Navbar.Collapse>
            <Nav>
                <RouteNavItem module={search.NAME} route={search.ROUTE}><Msg msg="search.title" /></RouteNavItem>
            </Nav>
            <Nav pullRight>
                <RouteNavItem module={about.NAME} route={about.ROUTE}><Msg msg="about.title" /></RouteNavItem>
                <RouteNavItem module={contact.NAME} route={contact.ROUTE}><Msg msg="contact.title" /></RouteNavItem>
            </Nav>
        </Navbar.Collapse>
    </Navbar>
);

export default Navigation;
