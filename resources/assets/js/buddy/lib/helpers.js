export const eventPhotosSort = (a, b) => {
    if (a.pivot.is_default == 'yes') {
        return -1;
    }
    if (b.pivot.is_default == 'yes') {
        return 1;
    }
    return a.id - b.id;
}


export const convertUtcToLocal = (idate) => {
    return moment.utc(idate).local().format('YYYY-MM-DD HH:mm:ss');
}