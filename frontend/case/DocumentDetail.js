import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import moment from 'moment';
import translate from '../translate';
import {DetailPanel} from '../components';
import {getDocument} from './selectors';

const mapStateToProps = (state, {id}) => {
    const document = getDocument(state, id);
    return {
        mark: document.mark,
        date: document.date,
        href: document.link,
        dateFormat: translate.getShortDateFormat(state),
    };
};

const mapDispatchToProps = () => ({
    openDetail: (href) => () => window.open(href),
});

const mergeProps = ({mark, date, href, dateFormat}, {openDetail}) => ({
    title: mark,
    footer: moment(date).format(dateFormat),
    onClick: openDetail(href),
    href,
});

const DocumentDetail = connect(mapStateToProps, mapDispatchToProps, mergeProps)(DetailPanel);

DocumentDetail.propTypes = {
    id: PropTypes.number.isRequired,
};

export default DocumentDetail;
