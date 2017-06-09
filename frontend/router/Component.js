import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {parse} from 'query-string';
import {routeEntered} from './actions';

export default (name) => (RouteComponent) => {
    const RouterComponent = class extends Component {
        componentDidMount() {
            this.setRoute();
        }
        componentDidUpdate() {
            this.setRoute();
        }
        setRoute() {
            this.props.onEnter(this.props.match.params, parse(this.props.location.search.slice(1)));
        }
        render() {
            return <RouteComponent />;
        }
    };
    RouterComponent.propTypes = {
        onEnter: PropTypes.func.isRequired,
        match: PropTypes.shape({
            params: PropTypes.object.isRequired,
        }).isRequired,
        location: PropTypes.shape({
            search: PropTypes.string.isRequired,
        }).isRequired,
    };
    RouterComponent.displayName = `Route(${name})`;

    const mapDispatchToProps = (dispatch) => ({
        onEnter: (params, query) => dispatch(routeEntered(name, params, query)),
    });
    return connect(undefined, mapDispatchToProps)(RouterComponent);
};
