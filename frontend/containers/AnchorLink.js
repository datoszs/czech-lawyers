import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {Element, scroller} from 'react-scroll';
import {browserHistory} from 'react-router';

class Anchor extends Component {
    componentDidMount() {
        this.scroll(window.location.hash, false);
        this.stopListening = browserHistory.listen((location) => this.scroll(location.hash, true));
    }

    componentWillUnmount() {
        this.stopListening();
    }

    scroll(hash, animate) {
        if (hash && hash.slice(1) === this.props.anchor) {
            scroller.scrollTo(this.props.anchor, animate ? {
                duration: 1000,
                delay: 100,
                smooth: true,
            } : {});
        }
    }

    render() {
        return <Element name={this.props.anchor} />;
    }
}

Anchor.propTypes = {
    anchor: PropTypes.string.isRequired,
};

export default Anchor;
