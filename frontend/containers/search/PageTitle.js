import {connect} from 'react-redux';
import PropTypes from 'prop-types';
import {PageTitle as PageTitleComponent} from '..';
import translate from '../../translate';

const mapStateToProps = (state, {msg, module}) => {
    const query = module.getQuery(state);
    if (query) {
        const title = translate.getMessage(state, msg);
        return {
            msg: 'search.title',
            title,
            query,
        };
    } else {
        return {msg};
    }
};

const PageTitle = connect(mapStateToProps)(PageTitleComponent);

PageTitle.propTypes = {
    msg: PropTypes.string.isRequired,
    module: PropTypes.shape({
        getQuery: PropTypes.func.isRequired,
    }).isRequired,
};

export default PageTitle;
