import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import {
    Router,
    Route,
    IndexRoute,
    browserHistory,
} from 'react-router';

import router from './router';
import navigation from './navigation';

import home from './home';
import about from './about';
import contact from './contact';
import search from './search';
import advocate from './advocate';
import caseDetail from './case';

const AppRouter = ({handleEnter, handleChange}) => {
    const createRoute = (module) => <Route
        path={module.ROUTE}
        component={module.Container}
        onEnter={handleEnter(module.NAME)}
        onChange={handleChange(module.NAME)}
    />;
    return (
        <Router history={browserHistory}>
            <Route path="/" component={navigation.AppContainer}>
                <IndexRoute component={home.Container} onEnter={handleEnter(home.NAME)} />
                {createRoute(about)}
                {createRoute(contact)}
                {createRoute(search)}
                {createRoute(advocate)}
                {createRoute(caseDetail)}
            </Route>
        </Router>
    );
};

AppRouter.propTypes = {
    handleEnter: PropTypes.func.isRequired,
    handleChange: PropTypes.func.isRequired,
};

const mapDispatchToProps = (dispatch) => ({
    handleEnter: (name) => (nextState) => dispatch(router.routeEntered(name, nextState.params, nextState.location.query)),
    handleChange: (name) => (prevState, nextState) => dispatch(router.routeEntered(name, nextState.params, nextState.location.query)),
});

export default connect(undefined, mapDispatchToProps)(AppRouter);
