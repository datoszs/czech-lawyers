import React from 'react';
import {
    Router,
    Route,
} from 'react-router-dom';
import history from './history';

import router from './router';
import navigation from './navigation';

import home from './home';
import about from './about';
import contact from './contact';
import advocateSearch from './advocatesearch';
import advocate from './advocate';
import caseDetail from './case';
import caseSearch from './casesearch';
import link from './link';

const createRoute = (module) => (
    <Route
        path={module.ROUTE}
        exact
        component={router.Component(module.NAME)(module.Container)}
    />
);

export default () => (
    <Router history={history}>
        <navigation.AppContainer>
            {createRoute(home)}
            {createRoute(about)}
            {createRoute(contact)}
            {createRoute(advocateSearch)}
            {createRoute(advocate)}
            {createRoute(caseSearch)}
            {createRoute(caseDetail)}
            {createRoute(link)}
        </navigation.AppContainer>
    </Router>
);
