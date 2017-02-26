import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import {
    Router as ReactRouter,
    Route,
    browserHistory,
} from 'react-router';

import router from './router';
import navigation from './navigation';

const Router = ({handleEnter}) => (
    <ReactRouter history={browserHistory}>
        <Route path="/" component={navigation.AppContainer} onEnter={handleEnter('ROOT')} >
            <Route path="about" component={() => <h1>O projektu</h1>} />
            <Route path="contact" component={() => <h1>Kontakt</h1>} />
        </Route>
    </ReactRouter>
);

Router.propTypes = {
    handleEnter: PropTypes.func.isRequired,
};

const mapDispatchToProps = (dispatch) => ({
    handleEnter: (name) => (nextState) => dispatch(router.routeEntered(name, nextState.params, nextState.location.query)),
});

export default connect(undefined, mapDispatchToProps)(Router);
