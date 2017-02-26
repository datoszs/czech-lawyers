import React from 'react';
import {Link} from 'react-router';
import {Navbar, Nav} from 'react-bootstrap';
import RouteNavItem from './RouteNavItem';

const Navigation = () => (
    <Navbar>
        <Navbar.Header>
            <Navbar.Brand><Link to="/">Čeští advokáti.cz</Link></Navbar.Brand>
            <Navbar.Toggle />
        </Navbar.Header>
        <Navbar.Collapse>
            <Nav pullRight>
                <RouteNavItem route="about">O projektu</RouteNavItem>
                <RouteNavItem route="contact">Kontakt</RouteNavItem>
            </Nav>
        </Navbar.Collapse>
    </Navbar>
);

export default Navigation;
