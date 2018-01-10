import React, {Fragment} from 'react';
import {Navbar, Nav} from 'react-bootstrap';
import RouteNavItem from './RouteNavItem';
import NssErrorAlert from './NssErrorAlert';

import {Msg, RouterLink} from '../containers';

import {HOME, ABOUT, CONTACT, ADVOCATE_SEARCH, CASE_SEARCH, EXPORT} from '../routes';

const Navigation = () => (
    <Fragment>
        <NssErrorAlert />
        <Navbar>
            <Navbar.Header>
                <Navbar.Brand><RouterLink route={HOME}><Msg msg="nav.title" /></RouterLink></Navbar.Brand>
                <Navbar.Toggle />
            </Navbar.Header>
            <Navbar.Collapse>
                <Nav>
                    <RouteNavItem module={ADVOCATE_SEARCH}><Msg msg="advocates.title" /></RouteNavItem>
                    <RouteNavItem module={CASE_SEARCH}><Msg msg="cases.title" /></RouteNavItem>
                </Nav>
                <Nav pullRight>
                    <RouteNavItem module={ABOUT}><Msg msg="about.title" /></RouteNavItem>
                    <RouteNavItem module={EXPORT}><Msg msg="export.nav" /></RouteNavItem>
                    <RouteNavItem module={CONTACT}><Msg msg="contact.title" /></RouteNavItem>
                </Nav>
            </Navbar.Collapse>
        </Navbar>
    </Fragment>
);

export default Navigation;
