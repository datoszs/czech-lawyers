import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import {
    Router as ReactRouter,
    Route,
    browserHistory,
} from 'react-router';

import router from './router';
import navigation from './navigation';

import about from './about';
import contact from './contact';

const Router = ({handleEnter}) => {
    const createRoute = (module) => <Route path={module.ROUTE} component={module.Container} onEnter={handleEnter(module.NAME)} />;
    return (
        <ReactRouter history={browserHistory}>
            <Route path="/" component={navigation.AppContainer}>
                {createRoute(about)}
                {createRoute(contact)}
            </Route>
        </ReactRouter>
    );
};

Router.propTypes = {
    handleEnter: PropTypes.func.isRequired,
};

const mapDispatchToProps = (dispatch) => ({
    handleEnter: (name) => (nextState) => dispatch(router.routeEntered(name, nextState.params, nextState.location.query)),
});

export default connect(undefined, mapDispatchToProps)(Router);
