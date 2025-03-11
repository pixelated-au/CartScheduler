const autoScroll = (el) => {
    el.scroll({top: el.scrollHeight});
};

export default {
    mounted: el => autoScroll(el),
    updated: el => autoScroll(el),
};
