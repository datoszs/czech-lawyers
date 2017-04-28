import React from 'react';
import {Link} from 'react-router';
import {Navbar, Nav} from 'react-bootstrap';
import RouteNavItem from './RouteNavItem';

import {Msg} from '../containers';

import about from '../about';
import contact from '../contact';
import advocateSearch from '../advocatesearch';
import caseSearch from '../casesearch';

const Navigation = () => (
    <Navbar>
        <Navbar.Header>
            <Navbar.Brand><Link to="/"><Msg msg="app.title" /></Link></Navbar.Brand>
            <Navbar.Toggle />
        </Navbar.Header>
        <Navbar.Collapse>
            <Nav>
                <RouteNavItem module={advocateSearch.NAME} route={advocateSearch.ROUTE}><Msg msg="advocates.title" /></RouteNavItem>
                <RouteNavItem module={caseSearch.NAME} route={caseSearch.ROUTE}><Msg msg="cases.title" /></RouteNavItem>
            </Nav>
            <Nav pullRight>
                <RouteNavItem module={about.NAME} route={about.ROUTE}><Msg msg="about.title" /></RouteNavItem>
                <RouteNavItem module={contact.NAME} route={contact.ROUTE}><Msg msg="contact.title" /></RouteNavItem>
            </Nav>
        </Navbar.Collapse>
    </Navbar>
);

export default Navigation;
