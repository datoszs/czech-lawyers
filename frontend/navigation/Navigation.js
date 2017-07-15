import React from 'react';
import {Link} from 'react-router-dom';
import {Navbar, Nav} from 'react-bootstrap';
import RouteNavItem from './RouteNavItem';

import {Msg, RouterLink} from '../containers';

import {HOME, ABOUT, CONTACT, ADVOCATE_SEARCH, CASE_SEARCH} from '../routes';

const Navigation = () => (
    <Navbar>
        <Navbar.Header>
            <Navbar.Brand><RouterLink route={HOME}><Msg msg="app.title" /></RouterLink></Navbar.Brand>
            <Navbar.Toggle />
        </Navbar.Header>
        <Navbar.Collapse>
            <Nav>
                <RouteNavItem module={ADVOCATE_SEARCH}><Msg msg="advocates.title" /></RouteNavItem>
                <RouteNavItem module={CASE_SEARCH}><Msg msg="cases.title" /></RouteNavItem>
            </Nav>
            <Nav pullRight>
                <RouteNavItem module={ABOUT}><Msg msg="about.title" /></RouteNavItem>
                <RouteNavItem module={CONTACT}><Msg msg="contact.title" /></RouteNavItem>
            </Nav>
        </Navbar.Collapse>
    </Navbar>
);

export default Navigation;
