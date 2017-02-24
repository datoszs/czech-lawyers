import React from 'react';
import {Link} from 'react-router';
import {Navbar, Nav, NavItem} from 'react-bootstrap';

const Navigation = () => (
    <Navbar>
        <Navbar.Header>
            <Navbar.Brand><Link to="/">Čeští advokáti.cz</Link></Navbar.Brand>
            <Navbar.Toggle />
        </Navbar.Header>
        <Navbar.Collapse>
            <Nav pullRight>
                <NavItem>O projektu</NavItem>
                <NavItem>Kontakt</NavItem>
            </Nav>
        </Navbar.Collapse>
    </Navbar>
);

export default Navigation;
