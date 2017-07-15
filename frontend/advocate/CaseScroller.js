import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Element, scroller} from 'react-scroll';
import {getCases} from './selectors';

class CaseScrollerComponent extends Component {
    constructor(props) {
        super(props);
        this.empty = false;
    }

    componentWillReceiveProps(nextProps) {
        if (this.props.name === nextProps.name && this.empty && !nextProps.empty) {
            setTimeout(() => {
                scroller.scrollTo(this.props.name);
            });
        }
        this.empty = nextProps.empty;
    }

    shouldComponentUpdate(nextProps) {
        return nextProps.name !== this.props.name;
    }

    render() {
        return <Element name={this.props.name} />;
    }
}

CaseScrollerComponent.propTypes = {
    empty: PropTypes.bool.isRequired,
    name: PropTypes.string.isRequired,
};

const mapStateToProps = (state) => ({
    empty: getCases(state).size === 0,
});

const CaseScroller = connect(mapStateToProps)(CaseScrollerComponent);

CaseScroller.propTypes = {
    name: PropTypes.string.isRequired,
};

export default CaseScroller;
