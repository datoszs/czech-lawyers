import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Element, scroller} from 'react-scroll';
import {getCases, getId} from './selectors';

class CaseScrollerComponent extends Component {
    constructor(props) {
        super(props);
        this.empty = false;
    }

    componentWillReceiveProps(nextProps) {
        const isSame = (prop) => (this.props[prop] === nextProps[prop]);
        if (isSame('name') && isSame('advocate') && this.empty && !nextProps.empty) {
            setTimeout(() => {
                scroller.scrollTo(this.props.name);
            });
            this.empty = nextProps.empty;
        } else {
            this.empty = false;
        }
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
    advocate: PropTypes.string, // eslint-disable-line react/no-unused-prop-types, linter error
};

CaseScrollerComponent.defaultProps = {
    advocate: null,
};

const mapStateToProps = (state) => ({
    empty: getCases(state).size === 0,
    advocate: getId(state),
});

const CaseScroller = connect(mapStateToProps)(CaseScrollerComponent);

CaseScroller.propTypes = {
    name: PropTypes.string.isRequired,
};

export default CaseScroller;
